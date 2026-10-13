<template>
  <FormSubmissions :form="form" />
</template>

<script setup>
import FormSubmissions from "~/components/open/forms/components/FormSubmissions.vue"

const props = defineProps({
  form: { type: Object, required: true },
})

definePageMeta({
  middleware: "auth",
})
useOpnSeoMeta({
  title: props.form
    ? "Respostas - " + props.form.title
    : "Respostas",
})


</script>
