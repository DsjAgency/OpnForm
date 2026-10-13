<template>
  <div class="flex flex-wrap px-4 py-1 -ml-1 -mt-1">
    <USelectMenu
      ref="ruleSelect"
      v-model="selectedRule"
      class="flex-grow ml-1 mr-1 mt-1"
      placeholder="Adicionar condição em um campo"
      :items="groupCtrl.rules"
      value-key="identifier"
      searchable
    />
    <UButton
      class="ml-1 mt-1"
      color="neutral"
      variant="outline"
      size="sm"
      :disabled="selectedRule === '' ? true : null"
      @click="addRule"
      label="Adicionar condição"
    />
    <UButton
      class="ml-1 mt-1"
      variant="outline"
      color="neutral"
      size="sm"
      @click="groupCtrl.newGroup"
      label="Adicionar grupo"
    />
  </div>
</template>

<script>
export default {
  components: {},
  props: { groupCtrl: { type: Object, required: true } },
  data() {
    return {
      selectedRule: null,
    }
  },
  methods: {
    addRule() {
      if (this.selectedRule) {
        this.groupCtrl.addRule(this.selectedRule)
        this.$refs.ruleSelect.content = null
        this.selectedRule = null
      } else {
        useAlert().error('Selecione um campo para adicionar a condição.')
      }
    },
  },
}
</script>
