<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Master Data', href: '/master-data' },
]);
import { ref, onMounted } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';

import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';

interface MasterRow {
  DATADATE: string;
  count: number;
  bakidebet: number;
}

const rows = ref<MasterRow[]>([]);
const loading = ref(false);
const search = ref('');
const meta = ref({ page: 1, per_page: 10, total: 0, last_page: 1 });
const showDeleteModal = ref(false);
const deleteDate = ref('');
const confirmDate = ref('');
const deleteError = ref('');

const fetchData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams({ search: search.value, page: String(meta.value.page), per_page: String(meta.value.per_page) });
    const res = await fetch(`/api/master-data?${params}`);
    if (!res.ok) throw new Error('Failed to fetch');
    const result = await res.json();
    rows.value = result.data;
    meta.value = result.meta;
  } catch (e) {
    // handle error
  } finally {
    loading.value = false;
  }
}

const handleDelete = async () => {
  if (confirmDate.value !== deleteDate.value) {
    deleteError.value = 'Nilai DATADATE tidak cocok.';
    return;
  }
  try {
    const res = await fetch(`/api/master-data/${deleteDate.value}`, { method: 'DELETE' });
    if (!res.ok) throw new Error('Gagal menghapus data');
    showDeleteModal.value = false;
    fetchData();
  } catch (e) {
    deleteError.value = 'Gagal menghapus data.';
  }
};

onMounted(fetchData);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Master Data" />
    <div class="space-y-6 p-6 bg-transparent">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight">Master Data</h1>
          <form @submit.prevent="fetchData" class="flex w-full max-w-sm items-center gap-1.5">
            <Input v-model="search" placeholder="Cari DATADATE..." />
            <Button type="submit">Cari</Button>
          </form>
        </div>
        <div class="overflow-x-auto">
          <Table>
          <TableHeader>
            <TableRow>
              <TableHead>DATADATE</TableHead>
              <TableHead>Jumlah Baris</TableHead>
              <TableHead>Baki Debet</TableHead>
              <TableHead>Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell :colspan="4" class="text-center">Memuat data...</TableCell>
            </TableRow>
            <TableRow v-else-if="rows.length === 0">
              <TableCell :colspan="4" class="text-center">Tidak ada data</TableCell>
            </TableRow>
            <TableRow v-else v-for="row in rows" :key="row.DATADATE">
              <TableCell>{{ row.DATADATE }}</TableCell>
              <TableCell>{{ row.count }}</TableCell>
              <TableCell>{{ row.bakidebet.toLocaleString('id-ID') }}</TableCell>
              <TableCell>
                <Button variant="destructive" size="sm" @click="() => { deleteDate = row.DATADATE; showDeleteModal = true; confirmDate = ''; deleteError = ''; }">Delete</Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" :disabled="meta.page === 1" @click="() => { meta.page--; fetchData(); }">Previous</Button>
        <span>Halaman {{ meta.page }} dari {{ meta.last_page }}</span>
        <Button variant="outline" size="sm" :disabled="meta.page === meta.last_page" @click="() => { meta.page++; fetchData(); }">Next</Button>
      </div>
      <Dialog v-model:open="showDeleteModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Konfirmasi Hapus Data</DialogTitle>
          </DialogHeader>
          <div class="space-y-2">
            <p>Yakin ingin menghapus semua data dengan DATADATE <b>{{ deleteDate }}</b>?</p>
            <p>Masukkan kembali nilai DATADATE untuk konfirmasi:</p>
            <input v-model="confirmDate" class="input input-bordered w-full" placeholder="Masukkan DATADATE" />
            <div v-if="deleteError" class="text-red-500 text-sm">{{ deleteError }}</div>
            <div class="flex gap-2 mt-2">
              <Button variant="destructive" @click="handleDelete">Hapus</Button>
              <Button variant="outline" @click="() => { showDeleteModal = false; }">Batal</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
