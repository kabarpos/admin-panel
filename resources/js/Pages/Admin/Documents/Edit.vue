<template>
  <AdminLayout title="Edit Dokumen" :user="$page.props.auth?.user">
    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-background-primary shadow-sm rounded-lg border border-border-light">
          <div class="p-6 bg-background-secondary dark:bg-background-tertiary shadow-sm">
            <div class="mb-6">
              <h2 class="text-xl font-semibold text-text-primary">Edit Dokumen</h2>
              <p class="mt-1 text-sm text-text-secondary">
                Perbarui informasi dokumen
              </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Nama Dokumen -->
              <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Nama Dokumen</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="block w-full mt-1 bg-background-tertiary border-border-light rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  required
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</div>
              </div>

              <!-- Deskripsi -->
              <div>
                <label for="description" class="block text-sm font-medium text-text-primary">Deskripsi</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="block w-full mt-1 bg-background-tertiary border-border-light rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                ></textarea>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</div>
              </div>

              <!-- File Info -->
              <div class="p-4 bg-background-secondary rounded-lg border border-border-light flex items-center">
                <div class="flex-shrink-0">
                  <DocumentIcon class="w-8 h-8 text-primary-500" />
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-text-primary">{{ document.file_name }}</h3>
                  <p class="text-xs text-text-tertiary">
                    {{ document.file_type }} · {{ document.formatted_file_size }}
                  </p>
                </div>
              </div>

              <!-- File Upload (Optional) -->
              <div>
                <label for="file" class="block text-sm font-medium text-text-primary">Ganti File (Opsional)</label>
                <div class="mt-1">
                  <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg border-border-light">
                    <div class="space-y-1 text-center">
                      <svg 
                        class="w-12 h-12 mx-auto text-text-tertiary" 
                        stroke="currentColor" 
                        fill="none" 
                        viewBox="0 0 48 48" 
                        aria-hidden="true"
                      >
                        <path 
                          d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" 
                          stroke-width="2" 
                          stroke-linecap="round" 
                          stroke-linejoin="round" 
                        />
                      </svg>
                      <div class="flex justify-center text-sm text-text-tertiary">
                        <label
                          for="file"
                          class="relative cursor-pointer bg-background-tertiary rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500"
                        >
                          <span>Upload file baru</span>
                          <input
                            id="file"
                            ref="fileInput"
                            type="file"
                            class="sr-only"
                            @change="handleFileUpload"
                          />
                        </label>
                        <p class="pl-1">atau seret dan letakkan</p>
                      </div>
                      <p v-if="form.file" class="text-sm text-text-primary py-2 px-3 bg-background-secondary rounded-lg mt-2">
                        {{ form.file.name }} ({{ formatFileSize(form.file.size) }})
                      </p>
                      <p class="text-xs text-text-tertiary">
                        Kosongkan jika tidak ingin mengganti file. Ukuran maksimum: 10MB.
                      </p>
                    </div>
                  </div>
                </div>
                <div v-if="form.errors.file" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.file }}</div>
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                <select
                  id="status"
                  v-model="form.status"
                  class="block w-full py-2 pl-3 pr-10 mt-1 bg-background-tertiary border-border-light rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                >
                  <option value="pending">Menunggu</option>
                  <option value="approved">Disetujui</option>
                  <option value="rejected">Ditolak</option>
                </select>
                <div v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</div>
              </div>

              <!-- Metadata -->
              <div>
                <div class="flex justify-between items-center">
                  <label for="metadata" class="block text-sm font-medium text-text-primary">Metadata</label>
                  <button
                    type="button"
                    @click="addMetadataField"
                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-100 dark:bg-primary-900 border border-transparent rounded-md hover:bg-primary-200 dark:hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                  >
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah
                  </button>
                </div>
                <div class="mt-2 space-y-3">
                  <div 
                    v-for="(field, index) in metadataFields" 
                    :key="index" 
                    class="flex items-center space-x-2 p-3 rounded-lg bg-background-secondary border border-border-light"
                  >
                    <div class="flex-1">
                      <input
                        v-model="field.key"
                        type="text"
                        placeholder="Kunci"
                        class="block w-full bg-background-tertiary border-border-light rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      />
                    </div>
                    <div class="flex-1">
                      <input
                        v-model="field.value"
                        type="text"
                        placeholder="Nilai"
                        class="block w-full bg-background-tertiary border-border-light rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      />
                    </div>
                    <button
                      type="button"
                      @click="removeMetadataField(index)"
                      class="p-1.5 rounded-md text-red-600 hover:text-red-900 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tombol Submit -->
              <div class="flex items-center justify-end space-x-3 pt-4">
                <Link
                  :href="route('admin.documents.index')"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-text-primary bg-background-tertiary border border-border-light rounded-lg shadow-sm hover:bg-background-secondary focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                  <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                  Kembali
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                  <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DocumentIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  document: Object,
});

// Form untuk edit dokumen
const form = useForm({
  name: props.document.name,
  description: props.document.description || '',
  file: null,
  status: props.document.status,
  metadata: props.document.metadata || {},
  _method: 'PUT',
});

// Metadata fields
const metadataFields = ref([]);

// Inisialisasi metadata fields dari data dokumen
onMounted(() => {
  if (props.document.metadata && Object.keys(props.document.metadata).length > 0) {
    metadataFields.value = Object.entries(props.document.metadata).map(([key, value]) => ({
      key,
      value,
    }));
  } else {
    metadataFields.value = [{ key: '', value: '' }];
  }
});

// File input reference
const fileInput = ref(null);

// Handling file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.file = file;
  }
};

// Fungsi format ukuran file
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Menambahkan metadata field
const addMetadataField = () => {
  metadataFields.value.push({ key: '', value: '' });
};

// Menghapus metadata field
const removeMetadataField = (index) => {
  metadataFields.value.splice(index, 1);
};

// Submit form
const submit = () => {
  // Mengumpulkan metadata
  const metadata = {};
  metadataFields.value.forEach((field) => {
    if (field.key && field.key.trim() !== '' && field.value !== undefined) {
      metadata[field.key.trim()] = field.value;
    }
  });
  
  form.metadata = Object.keys(metadata).length > 0 ? metadata : null;
  
  form.post(route('admin.documents.update', props.document.id), {
    preserveScroll: true,
    onSuccess: () => {
      if (form.file) {
        fileInput.value.value = '';
        form.file = null;
      }
    },
  });
};
</script> 