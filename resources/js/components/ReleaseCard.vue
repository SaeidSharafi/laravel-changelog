<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ChangeItem from './ChangeItem.vue'

const props = defineProps({
  release: { type: Object, default: null },
  isNew: { type: Boolean, default: false },
})

const { t } = useI18n({ useScope: 'global' })

const grouped = computed(() => {
  const changes = props.release?.changes ?? []
  return {
    feature: changes.filter((change) => change.type === 'feature'),
    improvement: changes.filter((change) => change.type === 'improvement'),
    fix: changes.filter((change) => change.type === 'fix'),
    generic: changes.filter((change) => !change.type),
  }
})
</script>

<template>
  <article v-if="release" class="flex flex-col gap-3">
    <header class="flex flex-wrap items-center gap-2">
      <h3 class="text-lg font-semibold leading-snug text-gray-900 dark:text-gray-100">
        {{ release.title }}
      </h3>
      <span
        v-if="isNew"
        class="inline-flex items-center rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white"
      >
        {{ t('changelog.new', 'New') }}
      </span>
      <span
        class="ms-auto inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-slate-800 dark:text-gray-300"
      >
        v{{ release.version }}
      </span>
    </header>

    <p class="text-sm text-gray-500 dark:text-gray-400">
      {{ release.date }}
    </p>

    <div class="flex flex-col gap-4">
      <section v-if="grouped.feature.length" class="flex flex-col gap-2">
        <span
          class="inline-flex items-center gap-1.5 self-start rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M12 3l2.4 5.2 5.6.8-4.1 3.9 1 5.6-4.9-2.6-4.9 2.6 1-5.6L4 9l5.6-.8L12 3z" />
          </svg>
          {{ t('changelog.features', 'Features') }}
        </span>
        <div class="flex flex-col gap-3">
          <ChangeItem
            v-for="(change, index) in grouped.feature"
            :key="`${change.title}-${index}`"
            :item="change"
          />
        </div>
      </section>

      <section v-if="grouped.improvement.length" class="flex flex-col gap-2">
        <span
          class="inline-flex items-center gap-1.5 self-start rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M3 17l6-6 4 4 8-8" />
            <path d="M15 7h6v6" />
          </svg>
          {{ t('changelog.improvements', 'Improvements') }}
        </span>
        <div class="flex flex-col gap-3">
          <ChangeItem
            v-for="(change, index) in grouped.improvement"
            :key="`${change.title}-${index}`"
            :item="change"
          />
        </div>
      </section>

      <section v-if="grouped.fix.length" class="flex flex-col gap-2">
        <span
          class="inline-flex items-center gap-1.5 self-start rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M14.7 6.3a4.5 4.5 0 0 0-6 5.6L3 17.6V21h3.4l5.7-5.7a4.5 4.5 0 0 0 5.6-6l-2.9 2.9-2.8-.6-.6-2.8 2.9-2.9z" />
          </svg>
          {{ t('changelog.fixes', 'Fixes') }}
        </span>
        <div class="flex flex-col gap-3">
          <ChangeItem
            v-for="(change, index) in grouped.fix"
            :key="`${change.title}-${index}`"
            :item="change"
          />
        </div>
      </section>

      <section v-if="grouped.generic.length" class="flex flex-col gap-2">
        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ t('changelog.changes', 'Changes') }}
        </span>
        <div class="flex flex-col gap-3">
          <ChangeItem
            v-for="(change, index) in grouped.generic"
            :key="`${change.title}-${index}`"
            :item="change"
          />
        </div>
      </section>
    </div>

    <section
      v-if="release.notes && release.notes.length"
      class="flex flex-col gap-1.5 border-t border-dashed border-gray-200 pt-3 dark:border-gray-700"
    >
      <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
        {{ t('changelog.notes', 'Notes') }}
      </span>
      <ul class="flex list-disc flex-col gap-1 ps-5 text-sm text-gray-600 dark:text-gray-300">
        <li v-for="note in release.notes" :key="note">
          {{ note }}
        </li>
      </ul>
    </section>
  </article>
</template>
