<script setup lang="tsx">
import { deleted, execute, list, save } from '$/mine-admin/crontab/api/crontab.ts'
import useDialog from '@/hooks/useDialog.ts'
import type { MaSearchItem } from '@mineadmin/search'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

import CrontabFrom from './form.vue'
import Logs from './logs.vue'

defineOptions({ name: 'plugin:mine-admin:crontab' })

const msg = useMessage()
const dictStore = useDictStore()
const formRef = ref()
const t = useTrans().globalTrans

const data = ref<{
  total: number
  list: any[]
}>({
  total: 10,
  list: [],
})

const formItems = ref<MaSearchItem[]>([
  {
    label: () => t('mineCrontab.cols.name'),
    prop: 'name',
    render: 'input',
    renderProps: {
      placeholder: t('form.pleaseInput', { msg: t('mineCrontab.cols.name') }),
    },
  },
  {
    label: () => t('mineCrontab.cols.type'),
    prop: 'type',
    render: () => <ma-dict-select dictName="crontab-type" />,
    renderProps: {
      placeholder: t('form.pleaseSelect', { msg: t('mineCrontab.cols.type') }),
    },
  },
  {
    label: () => t('mineCrontab.cols.status'),
    prop: 'status',
    render: () => <ma-dict-select dictName="system-status" />,
    renderProps: {
      placeholder: t('form.pleaseSelect', { msg: t('mineCrontab.cols.status') }),
    },
  },
])

function getCrontabList(params: Record<string, any> = {}) {
  list(params).then((res: any) => {
    data.value.total = res.data.total
    data.value.list = res.data.list as any[]
  }).catch((err) => {
    throw new Error(err)
  })
}

getCrontabList()

// 更新定时任务
function enableOrDisable(item: any) {
  item.status = !item.status
  save(item.id as number, item).then((res: any) => {
    res.code === ResultCode.SUCCESS ? msg.success(t('crud.updateSuccess')) : msg.error(res.message)
    getCrontabList()
  }).catch((err) => {
    throw new Error(err)
  })
}

// 执行定时任务
async function executeTask(id: number) {
  const response: any = await execute([id])
  console.log(response)
  if (response.code === ResultCode.SUCCESS) {
    msg.success(t('mineCrontab.op.executeSuccess'))
    getCrontabList()
  }
  else {
    msg.error(t('mineCrontab.op.executeFail'))
  }
}

// 删除
function handleDelete(id: number) {
  msg.delConfirm(t('crud.delDataMessage')).then(async () => {
    const response: any = await deleted([id])
    if (response.code === ResultCode.SUCCESS) {
      msg.success(t('crud.delSuccess'))
      getCrontabList()
    }
  })
}

// 弹窗配置
const maDialog: UseDialogExpose = useDialog({
  lgWidth: '750px',
  // 保存数据
  ok: ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    if (['add', 'edit'].includes(formType)) {
      const elForm = formRef.value.maForm.getElFormRef()
      // 验证通过后
      elForm.validate().then(() => {
        switch (formType) {
          // 新增
          case 'add':
            formRef.value.add().then((res: any) => {
              res.code === ResultCode.SUCCESS ? msg.success(t('crud.createSuccess')) : msg.error(res.message)
              maDialog.close()
              getCrontabList()
            }).catch((err: any) => {
              msg.alertError(err)
            })
            break
          // 修改
          case 'edit':
            formRef.value.edit().then((res: any) => {
              res.code === 200 ? msg.success(t('crud.updateSuccess')) : msg.error(res.message)
              maDialog.close()
              getCrontabList()
            }).catch((err: any) => {
              msg.alertError(err)
            })
            break
        }
      }).catch()
    }
    else {
      maDialog.close()
    }
    okLoadingState(false)
  },
})
</script>

<template>
  <div class="mine-card">
    <div>
      <ma-search
        :search-items="formItems"
        :options="{ cols: { xs: 1, sm: 2, md: 2, lg: 4, xl: 5 }, foldButtonShow: false }"
        @search="getCrontabList"
        @reset="getCrontabList"
      />
    </div>
    <ma-col-card>
      <template #card>
        <template v-for="item in data.list">
          <el-card shadow="hover">
            <div class="relative max-h-[240px] min-h-[220px]">
              <ma-svg-icon
                :size="400"
                :name="item.status ? 'material-symbols:play-arrow-rounded' : 'material-symbols:pause-rounded'"
                :class="item.status ? 'run-state' : 'pause-state'"
              />
              <div class="relative z-2">
                <div class="flex justify-between">
                  <span>{{ item.name }}</span>
                  <ElTag :type="dictStore.t('crontab-type', item.type, 'color')">
                    {{ t(dictStore.t('crontab-type', item.type, 'i18n')) }}
                  </ElTag>
                </div>
                <div class="text-sm">
                  <div class="memo">
                    {{ item.memo ?? '-' }}
                  </div>
                  <div class="mt-5 flex flex-col text-gray-6 dark-text-gray-3">
                    <span>{{ t('mineCrontab.cols.rule') }}：{{ item.rule }}</span>
                    <span>{{ t('mineCrontab.cols.value') }}：{{ item.value }}</span>
                    <span>
                      {{ t('mineCrontab.cols.singleton') }}：
                      <ma-svg-icon :name="item.is_singleton ? 'heroicons:check-16-solid' : 'heroicons:x-mark-16-solid'" class="relative top-[4px]" :size="18" />
                    </span>
                    <span>
                      {{ t('mineCrontab.cols.onOneServer') }}：
                      <ma-svg-icon :name="item.is_on_one_server ? 'heroicons:check-16-solid' : 'heroicons:x-mark-16-solid'" class="relative top-[4px]" :size="18" />
                    </span>
                  </div>
                  <!--                  <div v-if="item.status" class="text-green-6 dark-text-green-3"> -->
                  <!--                    {{ t('mineCrontab.cols.nextTime') }}：2025-04-01 00:00:00 -->
                  <!--                  </div> -->
                </div>
              </div>

              <div class="absolute w-full flex justify-between -bottom-[10px]">
                <div>
                  <el-button v-auth="['plugin:mine-admin:crontab:save']" circle>
                    <ma-svg-icon
                      :name="item.status ? 'material-symbols:play-arrow-rounded' : 'material-symbols:pause-rounded'"
                      :size="20"
                      @click="enableOrDisable(item)"
                    />
                  </el-button>
                  <el-button v-auth="['plugin:mine-admin:crontab:execute']" @click="executeTask(item.id)">
                    {{ t('mineCrontab.op.executeOnce') }}
                  </el-button>
                </div>
                <el-dropdown>
                  <el-button>{{ t('mineCrontab.op.other') }}</el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item
                        key="runLogs"
                        @click="() => {
                          maDialog.setTitle(`${item.name} - ${t('mineCrontab.op.runLogs')}`)
                          maDialog.open({ formType: null, crontabData: item })
                        }"
                      >
                        {{ t('mineCrontab.op.runLogs') }}
                      </el-dropdown-item>
                      <el-dropdown-item
                        v-if="hasAuth('plugin:mine-admin:crontab:save')"
                        key="edit"
                        @click="() => {
                          maDialog.setTitle((`${t('mineCrontab.op.edit')} - ${item.name}`))
                          maDialog.open({ formType: 'edit', crontabData: item })
                        }"
                      >
                        {{ t('mineCrontab.op.edit') }}
                      </el-dropdown-item>
                      <el-dropdown-item
                        v-if="hasAuth('plugin:mine-admin:crontab:delete')"
                        key="delete"
                        @click="handleDelete(item.id)"
                      >
                        {{ t('mineCrontab.op.delete') }}
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </div>
          </el-card>
        </template>

        <!-- 新增按钮 -->
        <div
          class="add-crontab"
          @click="() => {
            maDialog.setTitle(t('mineCrontab.op.add'))
            maDialog.open({ formType: 'add' })
          }"
        >
          <ma-svg-icon name="heroicons:plus-16-solid" :size="270" />
        </div>
      </template>
    </ma-col-card>
    <div class="mt-10 flex justify-end">
      <el-pagination layout="prev, pager, next, sizes" :total="data.total" :pager-count="5" />
    </div>

    <component :is="maDialog.Dialog">
      <template #default="{ formType, crontabData }">
        <!-- 新增、编辑任务表单 -->
        <CrontabFrom v-if="formType" ref="formRef" :form-type="formType" :crontab-data="crontabData" />
        <!-- 运行日志 -->
        <Logs v-if="!formType" :crontab-data="crontabData" />
      </template>
    </component>
  </div>
</template>

<style scoped lang="scss">
.run-state {
  @apply -top-25 text-green-8 -right-25 !absolute opacity-[0.1];
}
.pause-state {
  @apply -top-25 text-red-5 -right-27 !absolute opacity-[0.1];
}
.memo {
  @apply text-gray-4;
}
.add-crontab {
  @apply flex justify-center items-center b-1 b-solid b-gray-3 hover:b-[rgb(var(--ui-primary))] rounded transition-all duration-200 ease-in-out
  cursor-pointer dark-b-dark-1 dark-hover:b-[rgb(var(--ui-primary))] text-gray-5 hover:text-[rgb(var(--ui-primary))]
  ;
}
</style>
