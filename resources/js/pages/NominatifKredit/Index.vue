<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Nominatif Kredit', href: '/nominatif-kredit' },
]);
import { ref, reactive, onMounted, watch } from 'vue'
import { Eye } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'

import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'

interface NominatifKredit {
  id: number
  CAB: string
  NOMOR_REKENING: string
  NAMA_NASABAH: string
  POKOK_PINJAMAN: number
  KODE_KOLEK: string
  KET_KD_PRD: string
  AO: string
  TUNGGAKAN_BUNGA: number
  TUNGGAKAN_POKOK: number
}

interface FilterOptions {
  cabang: string[]
  ket_kd_prd: string[]
  kode_kolek: string[]
  ao: string[]
}

interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

const data = ref<NominatifKredit[]>([])
const loading = ref(false)
const selectedItem = ref<NominatifKredit | null>(null)
const isDetailModalOpen = ref(false)

const filters = reactive({
  search: '',
  cabang: '',
  ket_kd_prd: '',
  kode_kolek: '',
  ao: '',
  sort_by: '',
  sort_order: 'asc',
  per_page: 10,
  page: 1
})

const filterOptions = ref<FilterOptions>({
  cabang: [],
  ket_kd_prd: [],
  kode_kolek: [],
  ao: []
})

const meta = ref<PaginationMeta>({
  current_page: 1,
  from: 1,
  last_page: 1,
  per_page: 10,
  to: 10,
  total: 0
})

const sortableColumns = [
  { key: 'POKOK_PINJAMAN', label: 'Pokok Pinjaman' },
  { key: 'KODE_KOLEK', label: 'Kode Kolek' },
  { key: 'AO', label: 'AO' },
  { key: 'KET_KD_PRD', label: 'Product' },
  { key: 'TUNGGAKAN_POKOK', label: 'Tunggakan Pokok' },
  { key: 'TUNGGAKAN_BUNGA', label: 'Tunggakan Bunga' }
]

const fetchData = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([key, value]) => {
      if (value && value !== '') {
        params.append(key, String(value))
      }
    })
    const response = await fetch(`/api/nominatif-kredit?${params}`)
    if (!response.ok) throw new Error('Failed to fetch data')
    const result = await response.json()
    data.value = result.data
    meta.value = result.meta
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
}

const fetchFilterOptions = async () => {
  try {
    const response = await fetch('/api/nominatif-kredit-filter-options')
    if (!response.ok) throw new Error('Failed to fetch filter options')
    filterOptions.value = await response.json()
  } catch (error) {
    console.error('Error fetching filter options:', error)
  }
}

const showDetail = async (item: NominatifKredit) => {
  try {
    const response = await fetch(`/api/nominatif-kredit/${item.id}`)
    if (!response.ok) throw new Error('Failed to fetch detail')
    const result = await response.json()
    selectedItem.value = result.data
    isDetailModalOpen.value = true
  } catch (error) {
    console.error('Error fetching detail:', error)
  }
}

const handleSort = (column: string) => {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sort_by = column
    filters.sort_order = 'asc'
  }
  filters.page = 1
}

const handlePageChange = (page: number) => {
  filters.page = page
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    cabang: '',
    ket_kd_prd: '',
    kode_kolek: '',
    ao: '',
    sort_by: '',
    sort_order: 'asc',
    page: 1
  })
}

const paginationNumbers = computed(() => {
  const pages = []
  const current = meta.value.current_page
  const last = meta.value.last_page
  const delta = 2
  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i)
  }
  return pages
})

watch(() => filters, () => {
  filters.page = 1
  fetchData()
}, { deep: true })

watch(() => filters.page, () => {
  fetchData()
})

onMounted(() => {
  fetchData()
  fetchFilterOptions()
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Nominatif Kredit" />
  <div class="space-y-6 p-6 bg-transparent">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight">Nominatif Kredit</h1>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <form @submit.prevent="fetchData" class="flex w-full max-w-sm items-center gap-1.5">
            <Input v-model="filters.search" placeholder="Cari rekening, nama, CIF, AO..." />
            <Button type="submit">Cari</Button>
          </form>
          <Select v-model="filters.cabang" :options="[{label:'Semua Cabang',value:''},...filterOptions.cabang.map(c=>({label:c,value:c}))]" class="w-40" />
          <Select v-model="filters.ket_kd_prd" :options="[{label:'Semua Product',value:''},...filterOptions.ket_kd_prd.map(p=>({label:p,value:p}))]" class="w-40" />
          <Select v-model="filters.kode_kolek" :options="[{label:'Semua Kolek',value:''},...filterOptions.kode_kolek.map(k=>({label:k,value:k}))]" class="w-40" />
          <Select v-model="filters.ao" :options="[{label:'Semua AO',value:''},...filterOptions.ao.map(a=>({label:a,value:a}))]" class="w-40" />
          <Button variant="outline" @click="clearFilters">Reset Filter</Button>
        </div>
        <div class="overflow-x-auto">
          <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Cabang</TableHead>
            <TableHead>No. Rekening</TableHead>
            <TableHead>Nama Nasabah</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('POKOK_PINJAMAN')">Pokok Pinjaman</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('KODE_KOLEK')">Kode Kolek</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('KET_KD_PRD')">Product</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('AO')">AO</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('TUNGGAKAN_BUNGA')">Tunggakan Bunga</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('TUNGGAKAN_POKOK')">Tunggakan Pokok</TableHead>
            <TableHead>Aksi</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell :colspan="10" class="text-center">Memuat data...</TableCell>
          </TableRow>
          <TableRow v-else-if="data.length === 0">
            <TableCell :colspan="10" class="text-center">Tidak ada data</TableCell>
          </TableRow>
          <TableRow v-else v-for="item in data" :key="item.id">
            <TableCell>{{ item.CAB }}</TableCell>
            <TableCell>{{ item.NOMOR_REKENING }}</TableCell>
            <TableCell>{{ item.NAMA_NASABAH }}</TableCell>
            <TableCell>{{ item.POKOK_PINJAMAN.toLocaleString('id-ID') }}</TableCell>
            <TableCell>{{ item.KODE_KOLEK }}</TableCell>
            <TableCell>{{ item.KET_KD_PRD }}</TableCell>
            <TableCell>{{ item.AO }}</TableCell>
            <TableCell>{{ item.TUNGGAKAN_BUNGA.toLocaleString('id-ID') }}</TableCell>
            <TableCell>{{ item.TUNGGAKAN_POKOK.toLocaleString('id-ID') }}</TableCell>
            <TableCell>
              <Button variant="ghost" size="sm" @click="showDetail(item)"><Eye class="h-4 w-4" /></Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Menampilkan {{ meta.from }} sampai {{ meta.to }} dari {{ meta.total }} entri
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" :disabled="meta.current_page === 1" @click="handlePageChange(meta.current_page - 1)">Previous</Button>
        <div class="flex items-center gap-1">
          <Button v-for="page in paginationNumbers" :key="page" :variant="page === meta.current_page ? 'default' : 'outline'" size="sm" @click="handlePageChange(page)">{{ page }}</Button>
        </div>
        <Button variant="outline" size="sm" :disabled="meta.current_page === meta.last_page" @click="handlePageChange(meta.current_page + 1)">Next</Button>
      </div>
    </div>
  <Dialog v-model:open="isDetailModalOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Detail Debitur</DialogTitle>
        </DialogHeader>
        <div v-if="selectedItem">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div><b>Cabang:</b> {{ selectedItem.CAB }}</div>
              <div><b>No. Rekening:</b> {{ selectedItem.NOMOR_REKENING }}</div>
              <div><b>Nama Nasabah:</b> {{ selectedItem.NAMA_NASABAH }}</div>
              <div><b>AO:</b> {{ selectedItem.AO }}</div>
              <div><b>Kode Kolek:</b> {{ selectedItem.KODE_KOLEK }}</div>
              <div><b>Product:</b> {{ selectedItem.KET_KD_PRD }}</div>
            </div>
            <div>
              <div><b>Pokok Pinjaman:</b> {{ selectedItem.POKOK_PINJAMAN.toLocaleString('id-ID') }}</div>
              <div><b>Tunggakan Pokok:</b> {{ selectedItem.TUNGGAKAN_POKOK.toLocaleString('id-ID') }}</div>
              <div><b>Tunggakan Bunga:</b> {{ selectedItem.TUNGGAKAN_BUNGA.toLocaleString('id-ID') }}</div>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
    </div>
  </AppLayout>
</template>
