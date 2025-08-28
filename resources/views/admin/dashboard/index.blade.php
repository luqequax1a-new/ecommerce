@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">E-ticaret platformunuzun genel görünümü</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Today's Revenue -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600">Bugünkü Ciro</p>
                            <p class="text-2xl font-semibold text-gray-900" id="today-revenue">₺0,00</p>
                            <div class="mt-2">
                                <canvas id="revenue-sparkline" width="100" height="20" class="w-full h-5"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600">Bugünkü Sipariş</p>
                            <p class="text-2xl font-semibold text-gray-900" id="today-orders">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Carts -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-2.4 0L2 2h1m6 11v2a1 1 0 102 0v-2m1.9 0h1.2a1 1 0 01.9 1.2L18 20H6L4.2 15.2c-.1-.3-.1-.8.2-1.2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600">Aktif Sepet</p>
                            <div class="flex items-baseline">
                                <span class="text-xl font-semibold text-gray-900" id="active-carts-count">0</span>
                                <span class="ml-2 text-sm text-gray-500" id="active-carts-total">₺0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600">Stok Uyarıları</p>
                            <div class="flex space-x-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" id="low-stock-badge">0</span>
                                    <div class="text-xs text-gray-500 mt-1">Az Stok</div>
                                </div>
                                <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" id="out-stock-badge">0</span>
                                    <div class="text-xs text-gray-500 mt-1">Tükendi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Charts Section -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Sales Trend Chart -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Satış Trendi (Son 30 Gün)</h3>
                        <p class="text-sm text-gray-600">Günlük ciro ve sipariş sayısı</p>
                    </div>
                    <div class="p-6">
                        <div class="relative h-80">
                            <canvas id="sales-trend-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Order Status Distribution -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Sipariş Durum Dağılımı</h3>
                        <p class="text-sm text-gray-600">Son 30 güne ait sipariş durumları</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative h-64">
                                <canvas id="order-status-chart"></canvas>
                            </div>
                            <div id="order-status-legend" class="space-y-2">
                                <!-- Legend will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">En Çok Satan Ürünler</h3>
                        <p class="text-sm text-gray-600">Son 30 güne ait top 10 ürün</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Adı</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adet</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciro</th>
                                </tr>
                            </thead>
                            <tbody id="top-products-table" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Cron Jobs Widget -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Cron İşleri</h3>
                        <p class="text-sm text-gray-600">Sistem görevleri durumu</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Son Çalışanlar</h4>
                            <div id="cron-last-runs" class="space-y-2"></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Sıradakiler</h4>
                            <div id="cron-next-runs" class="space-y-2"></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-red-900 mb-3">Hatalı İşler</h4>
                            <div id="cron-failing" class="space-y-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Hızlı İşlemler</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('admin.products.create') }}" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors block text-center">
                            Yeni Ürün Ekle
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors block text-center">
                            Kategori Ekle
                        </a>
                        <a href="{{ route('admin.brands.create') }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors block text-center">
                            Marka Ekle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    loadKPIs();
    loadCharts();
    loadCronStatus();
}

async function loadKPIs() {
    try {
        const response = await fetch('/api/admin/metrics/today-kpis');
        const data = await response.json();
        
        document.getElementById('today-revenue').innerHTML = `₺${data.today_revenue.toLocaleString('tr-TR', {minimumFractionDigits: 2})}`;
        document.getElementById('today-orders').innerHTML = data.today_orders.toLocaleString('tr-TR');
        document.getElementById('active-carts-count').innerHTML = data.active_carts_count;
        document.getElementById('active-carts-total').innerHTML = `₺${data.active_carts_total.toLocaleString('tr-TR', {minimumFractionDigits: 2})}`;
        document.getElementById('low-stock-badge').innerHTML = data.low_stock_count;
        document.getElementById('out-stock-badge').innerHTML = data.out_of_stock_count;
    } catch (error) {
        console.error('Error loading KPIs:', error);
    }
}

async function loadCharts() {
    await loadSalesTrend();
    await loadOrderStatus();
    await loadTopProducts();
}

async function loadSalesTrend() {
    try {
        const response = await fetch('/api/admin/metrics/sales-trend?days=30');
        const data = await response.json();
        
        const ctx = document.getElementById('sales-trend-chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.series.map(item => item.date),
                datasets: [{
                    label: 'Ciro (₺)',
                    data: data.series.map(item => item.revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } catch (error) {
        console.error('Error loading sales trend:', error);
    }
}

async function loadOrderStatus() {
    try {
        const response = await fetch('/api/admin/metrics/order-status?days=30');
        const data = await response.json();
        
        const statusLabels = {
            'pending': 'Ödeme Bekliyor',
            'processing': 'Hazırlanıyor',
            'shipped': 'Kargoda',
            'delivered': 'Teslim Edildi'
        };
        
        const ctx = document.getElementById('order-status-chart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.data.map(item => statusLabels[item.status] || item.status),
                datasets: [{
                    data: data.data.map(item => item.count),
                    backgroundColor: ['#F59E0B', '#3B82F6', '#6366F1', '#10B981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } catch (error) {
        console.error('Error loading order status:', error);
    }
}

async function loadTopProducts() {
    try {
        const response = await fetch('/api/admin/metrics/top-products?days=30&limit=10');
        const data = await response.json();
        
        const tableBody = document.getElementById('top-products-table');
        tableBody.innerHTML = data.data.map(product => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.product}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.sku}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.units}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₺${product.revenue.toLocaleString('tr-TR', {minimumFractionDigits: 2})}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading top products:', error);
    }
}

async function loadCronStatus() {
    try {
        const response = await fetch('/api/admin/metrics/cron-summary');
        const data = await response.json();
        
        // Update cron sections
        document.getElementById('cron-last-runs').innerHTML = data.last_runs.map(run => `
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span class="text-sm">${run.task}</span>
                <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">${run.status}</span>
            </div>
        `).join('');
        
        document.getElementById('cron-next-runs').innerHTML = data.next_runs.map(run => `
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span class="text-sm">${run.task}</span>
                <span class="text-xs text-gray-500">${new Date(run.due_at).toLocaleString('tr-TR')}</span>
            </div>
        `).join('');
        
        document.getElementById('cron-failing').innerHTML = data.failing.map(fail => `
            <div class="p-2 bg-red-50 rounded">
                <div class="text-sm font-medium text-red-900">${fail.task}</div>
                <div class="text-xs text-red-700">${fail.last_error}</div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading cron status:', error);
    }
}
</script>
@endpush