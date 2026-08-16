<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import VideoEmbed from './VideoEmbed.vue'

defineProps({
  item: { type: Object, default: null },
})

const { t } = useI18n({ useScope: 'global' })
</script>

<template>
  <div v-if="item" class="flex flex-col gap-2">
    <p class="font-semibold text-gray-900 dark:text-gray-100">
      {{ item.title }}
    </p>

    <ul
      v-if="item.subtitles && item.subtitles.length"
      class="flex list-disc flex-col gap-1 ps-5 text-sm text-gray-600 dark:text-gray-300"
    >
      <li v-for="(subtitle, index) in item.subtitles" :key="`${subtitle}-${index}`">
        {{ subtitle }}
      </li>
    </ul>

    <img
      v-if="item.image"
      :src="`/${item.image}`"
      :alt="item.title"
      class="max-h-72 w-full rounded-lg border border-gray-200 object-cover dark:border-gray-700"
      loading="lazy"
    />

    <Link
      v-if="item.url"
      :href="route(item.url)"
      class="inline-flex w-fit items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
    >
      {{ t('changelog.tryIt', 'Try it') }}
    </Link>

    <VideoEmbed v-if="item.video" :video="item.video" />
  </div>
</template>
