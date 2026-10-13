<template>
  <div>
    <MentionInput
      v-model="compVal.message"
      :mentions="form.properties"
      :computed-variables="form.computed_variables"
      name="message"
      class="mt-4"
      label="Mensagem da notificação"
      help="Personalize o texto da notificação. Clique em @ para incluir valores dos campos do formulário."
    />
    <toggle-switch-input
      v-model="compVal.include_submission_data"
      name="include_submission_data"
      class="mt-4"
      label="Incluir dados da resposta"
      help="Com as respostas enviadas no formulário"
    />
    <toggle-switch-input
      v-if="compVal.include_submission_data"
      v-model="compVal.include_hidden_fields_submission_data"
      name="include_hidden_fields_submission_data"
      class="mt-4"
      label="Incluir campos ocultos"
      help="Se ativado, os campos ocultos serão incluídos na notificação"
    />
    <toggle-switch-input
      v-model="compVal.link_open_form"
      name="link_open_form"
      class="mt-4"
      label="Link 'Abrir formulário'"
      help="Link para a página pública do formulário"
    />
    <toggle-switch-input
      v-model="compVal.link_edit_form"
      name="link_edit_form"
      class="mt-4"
      label="Link 'Editar formulário'"
      help="Link para a página de administração do formulário"
    />
    <toggle-switch-input
      v-model="compVal.views_submissions_count"
      name="views_submissions_count"
      class="mt-4"
      label="Análises do formulário"
      help="Número de visualizações e respostas do formulário"
    />
    <toggle-switch-input
      v-if="form.editable_submissions"
      v-model="compVal.link_edit_submission"
      name="link_edit_submission"
      class="mt-4"
      label="Link para editar a resposta"
    />
  </div>
</template>

<script>
export default {
  name: "NotificationsMessageActions",
  components: {},
  props: {
    modelValue: { type: Object, required: false },
    form: { type: Object, required: true },
  },
  emits:  ['modelValue',  'input'],
  data() {
    return {
      content: this.modelValue ?? {},
    }
  },

  computed: {
    compVal: {
      set(val) {
        this.content = val
        this.$emit("input", this.compVal)
      },
      get() {
        return this.content
      },
    },
  },

  watch: {
    modelValue(val) {
      this.content = val
    },
  },

  created() {
    if (this.compVal === undefined || this.compVal === null) {
      this.compVal = {}
    }
    [
      "message",
      "include_submission_data",
      'include_hidden_fields_submission_data',
      "link_open_form",
      "link_edit_form",
      "views_submissions_count",
      "link_edit_submission",
    ].forEach((keyname) => {
      if (this.compVal[keyname] === undefined) {
        if (keyname === 'message') {
          this.compVal[keyname] = 'Nova resposta no formulário'
        } else if (['include_hidden_fields_submission_data'].includes(keyname)) {
          this.compVal[keyname] = false
        } else {
          this.compVal[keyname] = true
        }
      }
    })
  },

  methods: {},
}
</script>
