@extends('layouts.admin')

@section('title', 'Cron İşi Düzenle')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Cron İşi Düzenle</h1>
            <p class="mt-2 text-sm text-gray-700">"{{ $cronJob['name'] }}" cron işini düzenleyin.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('admin.cron.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:w-auto">
                Geri
            </a>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form id="cron-form" class="space-y-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="name" class="block text-sm font-medium text-gray-700">Cron İşi Adı</label>
                            <div class="mt-1">
                                <input type="text" name="name" id="name" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       value="{{ $cronJob['name'] }}" placeholder="Cache Temizleme">
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <fieldset>
                                <legend class="text-sm font-medium text-gray-700">Cron İşi Tipi</legend>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-center">
                                        <input id="type-command" name="type" type="radio" value="command" 
                                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                               {{ $cronJob['type'] === 'command' ? 'checked' : '' }}>
                                        <label for="type-command" class="ml-3 block text-sm font-medium text-gray-700">Komut</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="type-url" name="type" type="radio" value="url" 
                                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               {{ $cronJob['type'] === 'url' ? 'checked' : '' }}>
                                        <label for="type-url" class="ml-3 block text-sm font-medium text-gray-700">URL</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="sm:col-span-6" id="command-field" 
                             style="{{ $cronJob['type'] === 'command' ? '' : 'display: none;' }}">
                            <label for="command" class="block text-sm font-medium text-gray-700">Komut</label>
                            <div class="mt-1">
                                <select id="command" name="command" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Bir komut seçin</option>
                                    @foreach($commands as $key => $label)
                                        <option value="{{ $key }}" {{ $cronJob['command'] === $key ? 'selected' : '' }}>
                                            {{ $label }} ({{ $key }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Laravel artisan komutlarından birini seçin.</p>
                            </div>
                        </div>

                        <div class="sm:col-span-6" id="url-field" 
                             style="{{ $cronJob['type'] === 'url' ? '' : 'display: none;' }}">
                            <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                            <div class="mt-1">
                                <input type="url" name="url" id="url" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       value="{{ $cronJob['url'] ?? '' }}" placeholder="https://example.com/api/task">
                                <p class="mt-2 text-sm text-gray-500">Tam URL adresini girin.</p>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="cron_expression" class="block text-sm font-medium text-gray-700">CRON İfadesi</label>
                            <div class="mt-1">
                                <input type="text" name="cron_expression" id="cron_expression" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       value="{{ $cronJob['cron_expression'] }}" placeholder="0 2 * * *">
                                <p class="mt-2 text-sm text-gray-500">
                                    CRON ifadesi formatı: dakika saat gün ay gün. 
                                    <a href="https://crontab.guru/" target="_blank" class="text-indigo-600 hover:text-indigo-500">
                                        CRON ifadesi oluşturucu
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <div class="flex items-center">
                                <input id="is_active" name="is_active" type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ $cronJob['is_active'] ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="window.location='{{ route('admin.cron.index') }}'" 
                                class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            İptal
                        </button>
                        <button type="submit" 
                                class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cron Job Details -->
    <div class="mt-8 bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Cron İşi Detayları</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Son Çalıştırma</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $cronJob['last_run'] ? \Carbon\Carbon::parse($cronJob['last_run'])->format('d.m.Y H:i') : '-' }}
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Sonraki Çalıştırma</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $cronJob['next_run'] ? \Carbon\Carbon::parse($cronJob['next_run'])->format('d.m.Y H:i') : '-' }}
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Oluşturulma Tarihi</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($cronJob['created_at'])->format('d.m.Y H:i') }}
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Durum</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($cronJob['is_active'])
                            @if($cronJob['status'] === 'success')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    Aktif
                                </span>
                            @elseif($cronJob['status'] === 'running')
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                    Çalışıyor
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                    Hata
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                Pasif
                            </span>
                        @endif
                    </dd>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="flex space-x-3">
                    <button type="button" 
                            data-cron-id="{{ $cronJob['id'] }}"
                            class="execute-cron inline-flex items-center rounded-md border border-transparent bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Şimdi Çalıştır
                    </button>
                    <a href="{{ route('admin.cron.logs', $cronJob['id']) }}" 
                       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Logları Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between command and URL fields
    const typeCommand = document.getElementById('type-command');
    const typeUrl = document.getElementById('type-url');
    const commandField = document.getElementById('command-field');
    const urlField = document.getElementById('url-field');
    
    typeCommand.addEventListener('change', function() {
        if (this.checked) {
            commandField.style.display = '';
            urlField.style.display = 'none';
        }
    });
    
    typeUrl.addEventListener('change', function() {
        if (this.checked) {
            urlField.style.display = '';
            commandField.style.display = 'none';
        }
    });
    
    // Form submission
    document.getElementById('cron-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Convert is_active to boolean
        data.is_active = document.getElementById('is_active').checked;
        
        fetch('{{ route('admin.cron.update', $cronJob['id']) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Cron işi güncellenirken bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            alert('Cron işi güncellenirken bir hata oluştu: ' + error.message);
        });
    });
    
    // Execute cron job functionality
    document.querySelector('.execute-cron').addEventListener('click', function() {
        const cronId = this.getAttribute('data-cron-id');
        
        if (confirm('Bu cron işini şimdi çalıştırmak istediğinize emin misiniz?')) {
            fetch(`/admin/cron/${cronId}/execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cron işi başarıyla çalıştırıldı');
                    location.reload();
                } else {
                    alert('Cron işi çalıştırılırken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                alert('Cron işi çalıştırılırken bir hata oluştu: ' + error.message);
            });
        }
    });
});
</script>
@endsection