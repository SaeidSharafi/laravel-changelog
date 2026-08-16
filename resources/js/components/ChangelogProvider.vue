<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import ReleaseCard from './ReleaseCard.vue'

const { t } = useI18n({ useScope: 'global' })

const page = usePage()

const open = ref(false)
const mode = ref('drawer') // 'drawer' | 'spotlight'
const ackSent = ref(false)
const acknowledged = ref(false)

const changelog = computed(() => page.props.changelog)
const version = computed(() => page.props.version ?? '')
const unreadCount = computed(() => changelog.value?.unreadCount ?? 0)
const newReleases = computed(() => changelog.value?.newReleases ?? [])
const spotlightRelease = computed(() => newReleases.value.find((release) => release.highlight) ?? null)

const showBadge = computed(() => {
  return changelog.value !== null && changelog.value !== undefined && unreadCount.value > 0 && !acknowledged.value
})

function openDrawer() {
  mode.value = 'drawer'
  open.value = true
}

function openSurface(data) {
  const highlight = (data.newReleases ?? []).find((release) => release.highlight)
  mode.value = highlight ? 'spotlight' : 'drawer'
  open.value = true
}

function acknowledge() {
  if (ackSent.value) {
    return
  }
  ackSent.value = true
  acknowledged.value = true
  try {
    axios.post(route('acknowledge.changelog')).catch(() => {})
  } catch (error) {
    // Route helper unavailable — ignore; badge stays hidden for this session.
  }
}

function handleClose() {
  if (mode.value === 'spotlight') {
    // Spotlight closes into the drawer, which becomes the surface for the rest.
    mode.value = 'drawer'
  } else {
    open.value = false
  }
  acknowledge()
}

function onKeydown(event) {
  if (event.key === 'Escape' && open.value) {
    handleClose()
  }
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
  const data = page.props.changelog
  if (data && data.unreadCount > 0) {
    openSurface(data)
  }
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
})

watch(
  () => page.props.changelog,
  (newValue, oldValue) => {
    if (newValue === null || newValue === undefined) {
      ackSent.value = false
      acknowledged.value = false
      return
    }
    if ((oldValue === null || oldValue === undefined) && newValue.unreadCount > 0) {
      openSurface(newValue)
    }
  },
)
</script>

<template>
  <div>
    <!-- Floating unread badge. Physically right so it reads correctly in both LTR and RTL. -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 scale-75"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-75"
    >
      <button
        v-if="showBadge"
        type="button"
        class="fixed top-16 right-4 z-40 grid h-11 w-11 place-items-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-lg transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
        :aria-label="t('changelog.unread', 'Changelog')"
        @click="openDrawer"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
          <path d="M13.7 21a2 2 0 0 1-3.4 0" />
        </svg>
        <span
          class="absolute -top-1.5 -end-1.5 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold leading-none text-white"
        >
          {{ unreadCount }}
        </span>
      </button>
    </Transition>

    <!-- Drawer: physical right, slides in with translate-x-full. -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open && mode === 'drawer'" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" @click="handleClose" />
        <Transition
          enter-active-class="transition-transform duration-300 ease-out"
          enter-from-class="translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition-transform duration-200 ease-in"
          leave-from-class="translate-x-0"
          leave-to-class="translate-x-full"
        >
          <aside
            v-if="open && mode === 'drawer'"
            role="dialog"
            aria-modal="true"
            class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-s border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-slate-900"
          >
            <header class="flex items-center gap-3 border-b border-gray-200 px-4 py-4 dark:border-gray-700 sm:px-6">
              <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                {{ t('changelog.title', "What's New") }}
              </h2>
              <span
                v-if="version"
                class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300"
              >
                {{ version }}
              </span>
              <button
                type="button"
                class="ms-auto grid h-8 w-8 place-items-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-slate-800 dark:hover:text-gray-200"
                :aria-label="t('changelog.close', 'Close')"
                @click="handleClose"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  aria-hidden="true"
                >
                  <path d="M18 6 6 18" />
                  <path d="m6 6 12 12" />
                </svg>
              </button>
            </header>

            <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-6">
              <div class="flex flex-col gap-6">
                <ReleaseCard
                  v-for="(release, index) in newReleases"
                  :key="`${release.version}-${index}`"
                  :release="release"
                  :is-new="true"
                />
              </div>
            </div>
          </aside>
        </Transition>
      </div>
    </Transition>

    <!-- Spotlight: centered highlight release, scale + fade. -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open && mode === 'spotlight'" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="handleClose" />
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="open && mode === 'spotlight' && spotlightRelease"
            role="dialog"
            aria-modal="true"
            class="relative w-full max-w-2xl rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900 sm:p-6"
          >
            <div class="mb-4 flex items-center gap-2">
              <span
                class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300"
              >
                {{ t('changelog.spotlight', 'Spotlight') }}
              </span>
              <button
                type="button"
                class="ms-auto grid h-8 w-8 place-items-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-slate-800 dark:hover:text-gray-200"
                :aria-label="t('changelog.close', 'Close')"
                @click="handleClose"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  aria-hidden="true"
                >
                  <path d="M18 6 6 18" />
                  <path d="m6 6 12 12" />
                </svg>
              </button>
            </div>
            <ReleaseCard :release="spotlightRelease" :is-new="true" />
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>
