<script setup>
defineProps({
  video: { type: Object, default: null },
})
</script>

<template>
  <div v-if="video" class="flex flex-col">
    <iframe
      v-if="video.source === 'aparat'"
      :src="`https://www.aparat.com/video/video/embed/videohash/${video.id}/vt/frame`"
      class="aspect-video w-full rounded-lg border border-gray-200 dark:border-gray-700"
      title="Video"
      allow="autoplay; fullscreen; picture-in-picture"
      allowfullscreen
      loading="lazy"
    />
    <video
      v-else-if="video.source === 'mp4'"
      controls
      :src="video.src || video.id"
      class="aspect-video w-full rounded-lg bg-black"
    />
  </div>
</template>
