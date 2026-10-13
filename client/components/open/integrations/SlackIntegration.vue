<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <text-input
      :form="integrationData"
      name="data.slack_webhook_url"
      label="URL do webhook do Slack"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Receba uma mensagem no Slack a cada nova resposta.
            <a
              href="https://api.slack.com/messaging/webhooks"
              target="_blank"
            >
              Clique aqui
            </a>
            para saber como obter a URL de webhook do Slack
          </span>
        </InputHelp>
      </template>
    </text-input>
    <h4 class="font-bold mt-4">
      Ações da mensagem do Slack
    </h4>
    <notifications-message-actions
      v-model="integrationData.data"
      :form="form"
    />
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from "./components/IntegrationWrapper.vue"
import NotificationsMessageActions from "./components/NotificationsMessageActions.vue"

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})
</script>
