<script setup lang="ts">
const { crontabData = {} } = defineProps<{ crontabData: any }>()
const t = useTrans().globalTrans

const columns = ref([
  { label: () => t('mineCrontab.cols.name'), prop: 'name' },
  { label: () => t('mineCrontab.cols.status'), prop: 'status' },
  { label: () => t('mineCrontab.cols.value'), prop: 'target' },
  { label: () => t('mineCrontab.cols.exceptionInfo'), prop: 'exception_info' },
  { label: () => t('mineCrontab.cols.executeTime'), prop: 'created_at' },
])
</script>

<template>
  <ma-table :columns="columns" :data="crontabData?.execute_logs ?? []" />
</template>

<style scoped lang="scss">

</style>
