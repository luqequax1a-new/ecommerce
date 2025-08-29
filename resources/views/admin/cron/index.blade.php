@extends('layouts.admin')

@section('title', 'Cron İşleri Yönetimi')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Cron İşleri</h1>
            <p class="mt-2 text-sm text-gray-700">Sistemde tanımlı cron işlerini yönetin.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('admin.cron.create') }}" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Yeni Cron İşi
            </a>
        </div>
    </div>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Ad</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">Tip</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">Komut/URL</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">CRON İfadesi</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">Son Çalıştırma</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">Sonraki Çalıştırma</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-medium text-gray-900">Durum</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">İşlemler</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($cronJobs as $cronJob)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $cronJob['name'] }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                                        {{ $cronJob['type'] === 'command' ? 'Komut' : 'URL' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if($cronJob['type'] === 'command')
                                        <code>{{ $cronJob['command'] }}</code>
                                    @else
                                        <a href="{{ $cronJob['url'] }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">{{ Str::limit($cronJob['url'], 30) }}</a>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <code>{{ $cronJob['cron_expression'] }}</code>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $cronJob['last_run'] ? \Carbon\Carbon::parse($cronJob['last_run'])->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $cronJob['next_run'] ? \Carbon\Carbon::parse($cronJob['next_run'])->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
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
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" 
                                                data-cron-id="{{ $cronJob['id'] }}"
                                                class="execute-cron text-indigo-600 hover:text-indigo-900">
                                            Çalıştır
                                        </button>
                                        <a href="{{ route('admin.cron.edit', $cronJob['id']) }}" class="text-indigo-600 hover:text-indigo-900">Düzenle</a>
                                        <button type="button" 
                                                data-cron-id="{{ $cronJob['id'] }}"
                                                data-cron-name="{{ $cronJob['name'] }}"
                                                class="delete-cron text-red-600 hover:text-red-900">
                                            Sil
                                        </button>
                                        <button type="button" 
                                                data-cron-id="{{ $cronJob['id'] }}"
                                                data-is-active="{{ $cronJob['is_active'] ? 0 : 1 }}"
                                                class="toggle-status text-gray-600 hover:text-gray-900">
                                            {{ $cronJob['is_active'] ? 'Pasif Yap' : 'Aktif Yap' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Predefined Cron Tasks -->
    <div class="mt-12">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h2 class="text-xl font-semibold text-gray-900">Ön Tanımlı Cron İşleri</h2>
                <p class="mt-2 text-sm text-gray-700">Sistemin temel işlevleri için önerilen cron işleri.</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($predefinedTasks as $key => $task)
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-md bg-indigo-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ $task['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $task['description'] }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <code class="text-sm text-gray-600">{{ $task['cron_expression'] }}</code>
                        <p class="mt-1 text-sm text-gray-500">{{ $task['schedule'] }}</p>
                    </div>
                    <div class="mt-4">
                        <button type="button" 
                                data-task-key="{{ $key }}"
                                class="add-predefined-task inline-flex items-center rounded-md border border-transparent bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Ekle
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle status functionality
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const cronId = this.getAttribute('data-cron-id');
            const isActive = this.getAttribute('data-is-active');
            
            fetch(`/admin/cron/${cronId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_active: parseInt(isActive)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Durum değiştirilirken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                alert('Durum değiştirilirken bir hata oluştu: ' + error.message);
            });
        });
    });
    
    // Execute cron job functionality
    document.querySelectorAll('.execute-cron').forEach(button => {
        button.addEventListener('click', function() {
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
    
    // Delete cron job functionality
    document.querySelectorAll('.delete-cron').forEach(button => {
        button.addEventListener('click', function() {
            const cronId = this.getAttribute('data-cron-id');
            const cronName = this.getAttribute('data-cron-name');
            
            if (confirm(`"${cronName}" cron işini silmek istediğinize emin misiniz?`)) {
                fetch(`/admin/cron/${cronId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Cron işi silinirken bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Cron işi silinirken bir hata oluştu: ' + error.message);
                });
            }
        });
    });
    
    // Add predefined task functionality
    document.querySelectorAll('.add-predefined-task').forEach(button => {
        button.addEventListener('click', function() {
            const taskKey = this.getAttribute('data-task-key');
            
            fetch('{{ route('admin.cron.add-predefined-task') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    task_key: taskKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Önceden tanımlı cron işi başarıyla eklendi');
                    location.reload();
                } else {
                    alert('Önceden tanımlı cron işi eklenirken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                alert('Önceden tanımlı cron işi eklenirken bir hata oluştu: ' + error.message);
            });
        });
    });
});
</script>
@endsection