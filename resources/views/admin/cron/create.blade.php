@extends('layouts.admin')

@section('title', 'Yeni Cron İşi Oluştur')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Yeni Cron İşi</h1>
            <p class="mt-2 text-sm text-gray-700">Yeni bir cron işi oluşturun.</p>
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
                                       placeholder="Cache Temizleme">
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <fieldset>
                                <legend class="text-sm font-medium text-gray-700">Cron İşi Tipi</legend>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-center">
                                        <input id="type-command" name="type" type="radio" value="command" 
                                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                        <label for="type-command" class="ml-3 block text-sm font-medium text-gray-700">Komut</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="type-url" name="type" type="radio" value="url" 
                                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <label for="type-url" class="ml-3 block text-sm font-medium text-gray-700">URL</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="sm:col-span-6" id="command-field">
                            <label for="command" class="block text-sm font-medium text-gray-700">Komut</label>
                            <div class="mt-1">
                                <select id="command" name="command" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Bir komut seçin</option>
                                    @foreach($commands as $key => $label)
                                        <option value="{{ $key }}">{{ $label }} ({{ $key }})</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Laravel artisan komutlarından birini seçin.</p>
                            </div>
                        </div>

                        <div class="sm:col-span-6 hidden" id="url-field">
                            <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                            <div class="mt-1">
                                <input type="url" name="url" id="url" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="https://example.com/api/task">
                                <p class="mt-2 text-sm text-gray-500">Tam URL adresini girin.</p>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="cron_expression" class="block text-sm font-medium text-gray-700">CRON İfadesi</label>
                            <div class="mt-1">
                                <input type="text" name="cron_expression" id="cron_expression" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="0 2 * * *">
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
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
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
                            Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CRON Expression Help -->
    <div class="mt-8 bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">CRON İfadesi Formatı</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>CRON ifadeleri 5 alandan oluşur: dakika, saat, gün, ay, haftanın günü.</p>
            </div>
            <div class="mt-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Alan</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Açıklama</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Örnek</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Dakika</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0-59</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0, 15, 30, 45</td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Saat</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0-23</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0, 6, 12, 18</td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Gün</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1-31</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1, 15, 31</td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Ay</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1-12</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1, 6, 12</td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Haftanın Günü</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0-7 (0 ve 7 Pazar)</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1, 3, 5</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <h4 class="text-md font-medium text-gray-900">Yaygın CRON İfadeleri</h4>
                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">* * * * *</code>
                        <p class="mt-1 text-xs text-gray-500">Her dakika</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">0 * * * *</code>
                        <p class="mt-1 text-xs text-gray-500">Her saat başı</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">0 0 * * *</code>
                        <p class="mt-1 text-xs text-gray-500">Her gün gece yarısı</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">0 0 * * 0</code>
                        <p class="mt-1 text-xs text-gray-500">Her Pazar gece yarısı</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">0 0 1 * *</code>
                        <p class="mt-1 text-xs text-gray-500">Her ayın 1'inde gece yarısı</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <code class="text-sm font-medium text-gray-900">0 0 1 1 *</code>
                        <p class="mt-1 text-xs text-gray-500">Her yıl 1 Ocak'ta gece yarısı</p>
                    </div>
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
            commandField.classList.remove('hidden');
            urlField.classList.add('hidden');
        }
    });
    
    typeUrl.addEventListener('change', function() {
        if (this.checked) {
            urlField.classList.remove('hidden');
            commandField.classList.add('hidden');
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
        
        fetch('{{ route('admin.cron.store') }}', {
            method: 'POST',
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
                alert('Cron işi oluşturulurken bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            alert('Cron işi oluşturulurken bir hata oluştu: ' + error.message);
        });
    });
});
</script>
@endsection