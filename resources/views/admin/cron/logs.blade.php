@extends('layouts.admin')

@section('title', 'Cron İşi Logları')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">"{{ $cronJob['name'] }}" Logları</h1>
            <p class="mt-2 text-sm text-gray-700">Cron işi loglarını görüntüleyin.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('admin.cron.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:w-auto">
                Geri
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
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tarih</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Seviye</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Mesaj</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ \Carbon\Carbon::parse($log['created_at'])->format('d.m.Y H:i:s') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if($log['level'] === 'error')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Hata
                                        </span>
                                    @elseif($log['level'] === 'warning')
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Uyarı
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Bilgi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">{{ $log['message'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                    Henüz log kaydı bulunmamaktadır.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Summary -->
    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500">Bilgi</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ collect($logs)->where('level', 'info')->count() }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-yellow-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500">Uyarı</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ collect($logs)->where('level', 'warning')->count() }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-red-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500">Hata</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ collect($logs)->where('level', 'error')->count() }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500">Toplam</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ count($logs) }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cron Job Details -->
    <div class="mt-8 bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Cron İşi Detayları</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Ad</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $cronJob['name'] }}</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Tip</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                            {{ $cronJob['type'] === 'command' ? 'Komut' : 'URL' }}
                        </span>
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Komut/URL</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($cronJob['type'] === 'command')
                            <code>{{ $cronJob['command'] }}</code>
                        @else
                            <a href="{{ $cronJob['url'] }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                {{ Str::limit($cronJob['url'], 30) }}
                            </a>
                        @endif
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">CRON İfadesi</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <code>{{ $cronJob['cron_expression'] }}</code>
                    </dd>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="flex space-x-3">
                    <a href="{{ route('admin.cron.edit', $cronJob['id']) }}" 
                       class="inline-flex items-center rounded-md border border-transparent bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Düzenle
                    </a>
                    <button type="button" 
                            data-cron-id="{{ $cronJob['id'] }}"
                            class="execute-cron inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Şimdi Çalıştır
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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