export function isStale(event, now = Date.now()) {
  if (!event.email_tracking) return false
  return (event.status === "Processing" && now - Date.parse(event.updated_at) > 10 * 60 * 1000) ||
    Object.values(event.data?.email?.recipients || {}).some((recipient) =>
      ["pending", "sending"].includes(recipient.status) && now - Date.parse(recipient.updated_at) > 10 * 60 * 1000)

}

export function eventLabel(event) {
  if (event.status === "Error") return "Ação necessária"
  if (event.legacy_email && event.status === "Success") return "Concluído · entrega não rastreada"
  if (isStale(event)) return "Resultado desconhecido"
  return ({ Accepted: "Aceito · entrega não confirmada", Delivered: "Entregue ao servidor de e-mail", Unknown: "Resultado desconhecido", Error: "Ação necessária", Success: "Sucesso", Processing: "Processando" })[event.status] || event.status
}

export function eventColor(event) {
  if (event.status === "Error") return "error"
  if (isStale(event) || ["Unknown", "Accepted", "Processing"].includes(event.status)) return "warning"
  if (["Success", "Delivered"].includes(event.status)) return event.legacy_email ? "neutral" : "success"
  return event.status === "Error" ? "error" : "neutral"
}

export function recipientLabel(status) {
  return ({ pending: "Ainda não tentado", sending: "Enviando · resultado não confirmado", accepted: "Aceito pelo transporte · entrega não confirmada", delivered: "Entregue ao servidor de e-mail", invalid: "Endereço inválido · não enviado", blocked: "Cancelado pela aplicação · não enviado", not_sent: "Transporte sem entrega", unknown: "Resultado desconhecido", rejected: "Rejeitado pelo provedor de e-mail", bounced: "Devolvido", complained: "Marcado como spam" })[status] || status
}
