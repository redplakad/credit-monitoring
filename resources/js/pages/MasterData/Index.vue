<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, h } from 'vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';

import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';

interface MasterRow {
  DATADATE: string;
  count: number;
  bakidebet: number;
}

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Master Data', href: '/master-data' },
]);

const rows = ref<MasterRow[]>([]);
const loading = ref(false);
const search = ref('');
const meta = ref({ page: 1, per_page: 10, total: 0, last_page: 1 });

const showDeleteModal = ref(false);
const showImportModal = ref(false);

const importSchema = toTypedSchema(
  z.object({
    datadate: z.string().min(8, 'Minimal 8 karakter').max(8, 'Maksimal 8 karakter'),
    file: z.any().refine(val => val instanceof File, 'File wajib diupload'),
  })
);

function onImportSubmit(values: any) {
  toast.success('Import berhasil dimulai', {
    description: `DATADATE: ${values.datadate}, File: ${values.file?.name || 'N/A'}`,
  });
  showImportModal.value = false;
}

const deleteDate = ref('');
const confirmDate = ref('');
const deleteError = ref('');

const fetchData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      search: search.value,
      page: String(meta.value.page),
      per_page: String(meta.value.per_page),
    });
    const res = await fetch(`/api/master-data?${params}`);
    if (!res.ok) throw new Error('Failed to fetch');
    const result = await res.json();
    rows.value = result.data;
    meta.value = result.meta;
  } catch (e) {
    toast.error('Gagal memuat data', {
      description: 'Terjadi kesalahan saat memuat data master.',
    });
  } finally {
    loading.value = false;
  }
};

const handleDelete = async () => {
  if (confirmDate.value !== deleteDate.value) {
    deleteError.value = 'Nilai DATADATE tidak cocok.';
    return;
  }
  try {
    const res = await fetch(`/api/master-data/${deleteDate.value}`, { method: 'DELETE' });
    if (!res.ok) throw new Error('Gagal menghapus data');
    toast.success('Data berhasil dihapus', {
      description: `DATADATE ${deleteDate.value} telah dihapus.`,
    });
    showDeleteModal.value = false;
    fetchData();
  } catch (e) {
    deleteError.value = 'Gagal menghapus data.';
    toast.error('Gagal menghapus data', {
      description: 'Terjadi kesalahan saat menghapus data.',
    });
  }
};

// 🚀 auto-load data saat halaman dibuka
onMounted(() => {
  fetchData();
});
</script>

<template>
  <Toaster richColors closeButton />
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Master Data" />
    <div class="space-y-6 p-6 bg-transparent">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">Master Data</h1>
        <form @submit.prevent="fetchData" class="flex w-full max-w-sm items-center gap-1.5">
          <Input v-model="search" placeholder="Cari DATADATE..." />
          <Button type="submit">Cari</Button>
          <Button type="button" variant="secondary" class="ml-2" @click="showImportModal = true">
            Import
          </Button>
        </form>
      </div>

      <Dialog v-model:open="showImportModal">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Import Data Nominatif</DialogTitle>
            <DialogDescription>
              Pilih file dan masukkan DATADATE untuk import data nominatif.
            </DialogDescription>
          </DialogHeader>
          <Form v-slot="{ handleSubmit, setFieldValue }" :validation-schema="importSchema" keep-values>
            <form id="importDialogForm" @submit="handleSubmit($event, onImportSubmit)" class="space-y-4">
              <FormField v-slot="{ componentField }" name="datadate">
                <FormItem>
                  <FormLabel>DATADATE</FormLabel>
                  <FormControl>
                    <Input type="text" placeholder="YYYYMMDD" v-bind="componentField" />
                  </FormControl>
                  <FormDescription>Format: YYYYMMDD</FormDescription>
                  <FormMessage />
                </FormItem>
              </FormField>
              <FormField v-slot="{ componentField }" name="file">
                <FormItem>
                  <FormLabel>File</FormLabel>
                  <FormControl>
                    <Input type="file" @change="e => setFieldValue('file', e.target.files[0])" />
                  </FormControl>
                  <FormDescription>Upload file nominatif (.csv)</FormDescription>
                  <FormMessage />
                </FormItem>
              </FormField>
            </form>
          </Form>
          <DialogFooter>
            <Button type="submit" form="importDialogForm">
              Import
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

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
                <Button
                  variant="destructive"
                  size="sm"
                  @click="
                    () => {
                      deleteDate = row.DATADATE;
                      showDeleteModal = true;
                      confirmDate = '';
                      deleteError = '';
                    }
                  "
                  >Delete</Button
                >
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="meta.page === 1"
          @click="
            () => {
              meta.page--;
              fetchData();
            }
          "
          >Previous</Button
        >
        <span>Halaman {{ meta.page }} dari {{ meta.last_page }}</span>
        <Button
          variant="outline"
          size="sm"
          :disabled="meta.page === meta.last_page"
          @click="
            () => {
              meta.page++;
              fetchData();
            }
          "
          >Next</Button
        >
      </div>

      <Dialog v-model:open="showDeleteModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Konfirmasi Hapus Data</DialogTitle>
          </DialogHeader>
          <div class="space-y-2">
            <p>Yakin ingin menghapus semua data dengan DATADATE <b>{{ deleteDate }}</b>?</p>
            <p>Masukkan kembali nilai DATADATE untuk konfirmasi:</p>
            <Input
              v-model="confirmDate"
              class="w-full"
              placeholder="Masukkan DATADATE"
            />
            <div v-if="deleteError" class="text-red-500 text-sm">{{ deleteError }}</div>
            <div class="flex gap-2 mt-2">
              <Button variant="destructive" @click="handleDelete">Hapus</Button>
              <Button variant="outline" @click="() => { showDeleteModal = false }">Batal</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
