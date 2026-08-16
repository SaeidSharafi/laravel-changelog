<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ReleaseCard from './ReleaseCard.vue'

const props = defineProps({
  releases: { type: Array, default: () => [] },
})

const { t } = useI18n({ useScope: 'global' })

const page = usePage()

const version = computed(() => page.props.version ?? '')

const newVersions = computed(() => {
  const data = page.props.changelog
  if (!data || !data.newReleases) {
    return new Set()
  }
  return new Set(data.newReleases.map((release) => release.version))
})
</script>

<template>
  <div class="mx-auto flex w-full max-w-3xl flex-col gap-8 px-4 py-6 sm:px-6 sm:py-8">
    <header class="flex flex-col gap-1">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ t('changelog.title', "What's New") }}
      </h1>
      <p v-if="version" class="text-sm text-gray-500 dark:text-gray-400">
        v{{ version }}
      </p>
    </header>

    <div v-if="releases.length" class="relative flex flex-col gap-10 ps-6 sm:ps-8">
      <span
        class="absolute inset-y-0 start-0 w-0.5 bg-gray-200 dark:bg-gray-700"
        aria-hidden="true"
      />
      <div
        v-for="(release, index) in releases"
        :key="`${release.version}-${index}`"
        class="relative flex flex-col gap-2"
      >
        <span
          class="absolute -start-[15px] top-1.5 h-3.5 w-3.5 rounded-full border-2 border-indigo-600 bg-white dark:border-indigo-400 dark:bg-slate-900"
          aria-hidden="true"
        />
        <ReleaseCard
          :release="release"
          :is-new="newVersions.has(release.version)"
        />
      </div>
    </div>
  </div>
</template>
