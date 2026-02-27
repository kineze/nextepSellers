<template>
  <section class="mx-3 mt-3 mb-8 space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-fuchsia-600 dark:text-fuchsia-300">Learning</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Content Manager</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Manage content blocks and videos under each block.</p>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
        @click="openBlockDrawer()"
      >
        + Add Content Block
      </button>
    </div>

    <div v-if="loading" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      Loading content blocks...
    </div>

    <div v-else-if="!blocks.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      No content blocks found.
    </div>

    <div v-else class="grid gap-4 lg:grid-cols-2">
      <article v-for="block in blocks" :key="block.id" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/50">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ block.title }}</h2>
              <span v-if="block.default_block" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Default</span>
            </div>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">{{ block.description || 'No description' }}</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" @click="openBlockDrawer(block)"><i class="fas fa-pen"></i></button>
            <button type="button" class="text-rose-500 hover:text-rose-700" @click="deleteBlock(block)"><i class="fas fa-trash"></i></button>
          </div>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/70">
          <div class="mb-2 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Videos</h3>
            <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800" @click="openVideoDrawer(block)">
              + Add Video
            </button>
          </div>

          <div v-if="!block.videos?.length" class="rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No videos under this block.
          </div>

          <div v-else class="space-y-2">
            <div v-for="video in block.videos" :key="video.id" class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-800/60">
              <div class="grid gap-3 sm:grid-cols-[220px_minmax(0,1fr)]">
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900">
                  <iframe
                    :src="toEmbedUrl(video.embedded_link)"
                    class="h-36 w-full"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                  ></iframe>
                </div>

                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ video.title }}</p>
                    <a :href="video.embedded_link" target="_blank" rel="noopener" class="block truncate text-xs text-blue-600 hover:underline dark:text-blue-300">{{ video.embedded_link }}</a>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">{{ video.description || 'No description' }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" @click="openVideoDrawer(block, video)"><i class="fas fa-pen"></i></button>
                    <button type="button" class="text-rose-500 hover:text-rose-700" @click="deleteVideo(block, video)"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </article>
    </div>

    <transition name="fade">
      <div v-if="showBlockDrawer" class="fixed inset-0 z-[1500] flex justify-end bg-slate-900/60 backdrop-blur-sm">
        <div class="flex h-screen w-full max-w-md flex-col border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingBlockId ? 'Edit Content Block' : 'Create Content Block' }}</h3>
            <button @click="closeBlockDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <form class="space-y-4" @submit.prevent="saveBlock">
            <label class="space-y-1">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Title</span>
              <input v-model.trim="blockForm.title" type="text" required placeholder="Example: Seller Onboarding Basics" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-fuchsia-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
            </label>
            <label class="space-y-1">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Description</span>
              <textarea v-model.trim="blockForm.description" rows="4" placeholder="Short summary about this learning block..." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-fuchsia-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
              <input v-model="blockForm.default_block" type="checkbox" class="h-4 w-4 rounded border border-slate-300" />
              Mark as default block
            </label>
            <button type="submit" :disabled="savingBlock" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black disabled:opacity-60 dark:bg-white dark:text-slate-900">
              {{ savingBlock ? 'Saving...' : editingBlockId ? 'Update Block' : 'Create Block' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showVideoDrawer" class="fixed inset-0 z-[1500] flex justify-end bg-slate-900/60 backdrop-blur-sm">
        <div class="flex h-screen w-full max-w-md flex-col border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingVideoId ? 'Edit Video' : 'Create Video' }}</h3>
            <button @click="closeVideoDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <form class="space-y-4" @submit.prevent="saveVideo">
            <label class="space-y-1">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Title</span>
              <input v-model.trim="videoForm.title" type="text" required placeholder="Example: How to Create Your First Order" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-fuchsia-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
            </label>
            <label class="space-y-1">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Embedded Link</span>
              <input v-model.trim="videoForm.embedded_link" type="url" required placeholder="https://..." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-fuchsia-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
            </label>
            <label class="space-y-1">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Description</span>
              <textarea v-model.trim="videoForm.description" rows="4" placeholder="Explain what this video teaches..." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-fuchsia-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
            </label>
            <button type="submit" :disabled="savingVideo" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black disabled:opacity-60 dark:bg-white dark:text-slate-900">
              {{ savingVideo ? 'Saving...' : editingVideoId ? 'Update Video' : 'Create Video' }}
            </button>
          </form>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const blocks = ref([])

const showBlockDrawer = ref(false)
const editingBlockId = ref(null)
const savingBlock = ref(false)
const blockForm = reactive({
  title: '',
  description: '',
  default_block: false,
})

const showVideoDrawer = ref(false)
const editingVideoId = ref(null)
const selectedBlockId = ref(null)
const savingVideo = ref(false)
const videoForm = reactive({
  title: '',
  embedded_link: '',
  description: '',
})

const fetchBlocks = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/learning/content-blocks')
    blocks.value = Array.isArray(data?.blocks) ? data.blocks : []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load content blocks.')
  } finally {
    loading.value = false
  }
}

const resetBlockForm = () => {
  blockForm.title = ''
  blockForm.description = ''
  blockForm.default_block = false
}

const openBlockDrawer = (block = null) => {
  resetBlockForm()
  editingBlockId.value = null

  if (block) {
    editingBlockId.value = block.id
    blockForm.title = block.title || ''
    blockForm.description = block.description || ''
    blockForm.default_block = !!block.default_block
  }

  showBlockDrawer.value = true
}

const closeBlockDrawer = () => {
  showBlockDrawer.value = false
  editingBlockId.value = null
  resetBlockForm()
}

const saveBlock = async () => {
  savingBlock.value = true
  try {
    const payload = {
      title: blockForm.title,
      description: blockForm.description || null,
      default_block: !!blockForm.default_block,
    }

    if (editingBlockId.value) {
      await axios.put(`/api/learning/content-blocks/${editingBlockId.value}`, payload)
      toast.success('Content block updated.')
    } else {
      await axios.post('/api/learning/content-blocks', payload)
      toast.success('Content block created.')
    }

    closeBlockDrawer()
    fetchBlocks()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save content block.')
  } finally {
    savingBlock.value = false
  }
}

const deleteBlock = async (block) => {
  if (!window.confirm(`Delete content block \"${block.title}\"?`)) return

  try {
    await axios.delete(`/api/learning/content-blocks/${block.id}`)
    toast.success('Content block deleted.')
    fetchBlocks()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete content block.')
  }
}

const resetVideoForm = () => {
  videoForm.title = ''
  videoForm.embedded_link = ''
  videoForm.description = ''
}

const openVideoDrawer = (block, video = null) => {
  selectedBlockId.value = block.id
  editingVideoId.value = null
  resetVideoForm()

  if (video) {
    editingVideoId.value = video.id
    videoForm.title = video.title || ''
    videoForm.embedded_link = video.embedded_link || ''
    videoForm.description = video.description || ''
  }

  showVideoDrawer.value = true
}

const closeVideoDrawer = () => {
  showVideoDrawer.value = false
  selectedBlockId.value = null
  editingVideoId.value = null
  resetVideoForm()
}

const saveVideo = async () => {
  if (!selectedBlockId.value) {
    toast.error('Select a content block first.')
    return
  }

  savingVideo.value = true
  try {
    const payload = {
      title: videoForm.title,
      embedded_link: videoForm.embedded_link,
      description: videoForm.description || null,
    }

    if (editingVideoId.value) {
      await axios.put(`/api/learning/content-blocks/${selectedBlockId.value}/videos/${editingVideoId.value}`, payload)
      toast.success('Video updated.')
    } else {
      await axios.post(`/api/learning/content-blocks/${selectedBlockId.value}/videos`, payload)
      toast.success('Video created.')
    }

    closeVideoDrawer()
    fetchBlocks()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save video.')
  } finally {
    savingVideo.value = false
  }
}

const deleteVideo = async (block, video) => {
  if (!window.confirm(`Delete video \"${video.title}\"?`)) return

  try {
    await axios.delete(`/api/learning/content-blocks/${block.id}/videos/${video.id}`)
    toast.success('Video deleted.')
    fetchBlocks()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete video.')
  }
}

const toEmbedUrl = (url) => {
  const raw = String(url || '').trim()
  if (!raw) return ''

  try {
    const parsed = new URL(raw)
    const host = parsed.hostname.replace(/^www\./, '')

    if (host === 'youtube.com' || host === 'm.youtube.com' || host === 'youtu.be') {
      if (host === 'youtu.be') {
        const id = parsed.pathname.replace('/', '')
        return id ? `https://www.youtube.com/embed/${id}` : raw
      }

      if (parsed.pathname === '/watch') {
        const id = parsed.searchParams.get('v')
        return id ? `https://www.youtube.com/embed/${id}` : raw
      }

      if (parsed.pathname.startsWith('/embed/')) {
        return raw
      }
    }

    if (host === 'vimeo.com') {
      const id = parsed.pathname.split('/').filter(Boolean)[0]
      return id ? `https://player.vimeo.com/video/${id}` : raw
    }

    return raw
  } catch {
    return raw
  }
}

onMounted(() => {
  fetchBlocks()
})
</script>
