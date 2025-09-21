<template>
  <div class="relative w-full">
    <button
      type="button"
      class="w-full border rounded px-2 py-1 text-sm flex items-center justify-between bg-white"
      @click="toggleDropdown"
      :aria-expanded="open"
    >
      <span class="truncate text-left">
        <template v-if="selectedOptions.length === 0">Pilih {{ label }}</template>
        <template v-else>{{ selectedOptions.map(o => o).join(', ') }}</template>
      </span>
      <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div v-if="open" class="absolute z-50 mt-1 w-full bg-white border rounded shadow-lg max-h-60 overflow-auto">
      <div class="p-2">
        <input v-model="search" type="text" class="w-full border rounded px-2 py-1 text-sm mb-2" :placeholder="'Cari ' + label" />
      </div>
      <ul>
        <li v-for="option in filteredOptions" :key="option" class="px-2 py-1 hover:bg-gray-100 flex items-center">
          <input type="checkbox" :id="optionKey(option)" :value="option" v-model="modelValue" class="mr-2" />
          <label :for="optionKey(option)" class="cursor-pointer">{{ option }}</label>
        </li>
        <li v-if="filteredOptions.length === 0" class="px-2 py-1 text-gray-400">Tidak ada data</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps<{
  modelValue: string[]
  options: string[]
  label: string
}>()
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const search = ref('')

const filteredOptions = computed(() => {
  if (!search.value) return props.options
  return props.options.filter(o => o.toLowerCase().includes(search.value.toLowerCase()))
})

const selectedOptions = computed(() => props.modelValue)

function toggleDropdown() {
  open.value = !open.value
}

function closeDropdown(e: MouseEvent) {
  if (!(e.target as HTMLElement).closest('.relative')) {
    open.value = false
  }
}

function optionKey(option: string) {
  return `${props.label}-${option}`
}

watch(() => props.modelValue, (val) => {
  emit('update:modelValue', val)
})

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})
</script>

<style scoped>
.relative { position: relative; }
</style>
