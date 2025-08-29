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
                            <p class="text-2xl font-semibold text-gray-900" id="today-revenue">
                <span class="skeleton-loader inline-block bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%] animate-pulse rounded h-8 w-24"></span>
            </p>
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
                            <p class="text-2xl font-semibold text-gray-900" id="today-orders">
                <span class="skeleton-loader inline-block bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%] animate-pulse rounded h-8 w-16"></span>
            </p>
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
                                <span class="text-xl font-semibold text-gray-900" id="active-carts-count">
                                    <span class="skeleton-loader inline-block bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%] animate-pulse rounded h-6 w-8"></span>
                                </span>
                                <span class="ml-2 text-sm text-gray-500" id="active-carts-total">
                                    <span class="skeleton-loader inline-block bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%] animate-pulse rounded h-4 w-16"></span>
                                </span>
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" id="low-stock-badge">
                                        <span class="skeleton-loader inline-block bg-yellow-200 rounded h-3 w-6"></span>
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">Az Stok</div>
                                </div>
                                <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" id="out-stock-badge">
                                        <span class="skeleton-loader inline-block bg-red-200 rounded h-3 w-6"></span>
                                    </span>
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
<style>
.skeleton-loader {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
}

@media (max-width: 768px) {
    .chart-container {
        height: 250px !important;
    }
    
    .mobile-hide {
        display: none;
    }
    
    .mobile-stack {
        flex-direction: column;
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

// Global variables
let charts = {};
let retryAttempts = {};
const MAX_RETRIES = 3;
let isLoading = false;

function initializeDashboard() {
    showLoadingStates();
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
    
    // Add mobile responsive event listeners
    window.addEventListener('resize', handleResize);
    handleResize();
}

// Enhanced error handling and retry logic
async function fetchWithRetry(url, retries = 0) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response;
    } catch (error) {
        if (retries < MAX_RETRIES) {
            console.warn(`Retrying ${url} (attempt ${retries + 1}/${MAX_RETRIES})`);
            await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, retries)));
            return fetchWithRetry(url, retries + 1);
        }
        throw error;
    }
}

// Handle responsive design
function handleResize() {
    const isMobile = window.innerWidth < 768;
    
    // Update chart responsiveness
    Object.values(charts).forEach(chart => {
        if (chart && chart.options) {
            chart.options.plugins.legend.position = isMobile ? 'bottom' : 'top';
            chart.options.scales.x.title.display = !isMobile;
            chart.options.scales.y.title.display = !isMobile;
            if (chart.options.scales.y1) {
                chart.options.scales.y1.display = !isMobile;
            }
            chart.update();
        }
    });
}

// Show empty state
function showEmptyState(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        const parent = element.parentElement;
        parent.innerHTML = `
            <div class="empty-state">
                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-gray-500 text-sm">${message}</p>
                <button onclick="loadDashboardData()" class="mt-3 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                    Yeniden Dene
                </button>
            </div>
        `;
    }
}

// Show chart error
function showChartError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        const parent = element.parentElement;
        parent.innerHTML = `
            <div class="error-state">
                <svg class="w-12 h-12 text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-500 text-sm mb-2">${message}</p>
                <button onclick="loadCharts()" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                    Yeniden Dene
                </button>
            </div>
        `;
    }
}

// Enhanced loading states
function showLoadingStates() {
    // KPI loading states are handled in HTML with skeleton classes
    showChartSkeletons();
    showTableSkeleton();
    showCronSkeleton();
}

function showChartSkeletons() {
    const chartContainers = ['sales-trend-chart', 'order-status-chart'];
    chartContainers.forEach(id => {
        const container = document.getElementById(id);
        if (container) {
            container.style.display = 'none';
            const parent = container.parentElement;
            if (!parent.querySelector('.chart-skeleton')) {
                const skeleton = createChartSkeleton();
                parent.appendChild(skeleton);
            }
        }
    });
}

function createChartSkeleton() {
    const skeleton = document.createElement('div');
    skeleton.className = 'chart-skeleton flex items-center justify-center h-80';
    skeleton.innerHTML = `
        <div class="animate-pulse w-full">
            <div class="h-4 bg-gray-200 rounded w-32 mb-4 mx-auto"></div>
            <div class="space-y-2">
                <div class="h-2 bg-gray-200 rounded w-full"></div>
                <div class="h-2 bg-gray-200 rounded w-5/6"></div>
                <div class="h-2 bg-gray-200 rounded w-4/5"></div>
                <div class="h-2 bg-gray-200 rounded w-full"></div>
                <div class="h-2 bg-gray-200 rounded w-3/4"></div>
            </div>
        </div>
    `;
    return skeleton;
}

function showTableSkeleton() {
    const tableBody = document.getElementById('top-products-table');
    if (tableBody) {
        tableBody.innerHTML = Array(5).fill(0).map(() => `
            <tr class="animate-pulse">
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-32"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-12"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
            </tr>
        `).join('');
    }
}

function showCronSkeleton() {
    const cronSections = ['cron-last-runs', 'cron-next-runs', 'cron-failing'];
    cronSections.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = `
                <div class="animate-pulse">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded mb-2">
                        <div class="h-4 bg-gray-200 rounded w-24"></div>
                        <div class="h-3 bg-gray-200 rounded w-12"></div>
                    </div>
                </div>
            `;
        }
    });
}

// Main data loading function
async function loadDashboardData() {
    if (isLoading) return;
    isLoading = true;
    
    try {
        await Promise.allSettled([
            loadKPIs(),
            loadCharts(),
            loadCronStatus()
        ]);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    } finally {
        isLoading = false;
        hideLoadingStates();
    }
}

function hideLoadingStates() {
    document.querySelectorAll('.chart-skeleton').forEach(el => el.remove());
    document.querySelectorAll('canvas').forEach(canvas => {
        canvas.style.display = 'block';
    });
}

async function loadKPIs() {
    try {
        const response = await fetchWithRetry('/api/admin/metrics/today-kpis');
        const data = await response.json();
        
        updateKPIElement('today-revenue', `₺${data.today_revenue.toLocaleString('tr-TR', {minimumFractionDigits: 2})}`);
        updateKPIElement('today-orders', data.today_orders.toLocaleString('tr-TR'));
        updateKPIElement('active-carts-count', data.active_carts_count);
        updateKPIElement('active-carts-total', `₺${data.active_carts_total.toLocaleString('tr-TR', {minimumFractionDigits: 2})}`);
        updateKPIElement('low-stock-badge', data.low_stock_count);
        updateKPIElement('out-stock-badge', data.out_of_stock_count);
        
    } catch (error) {
        console.error('Error loading KPIs:', error);
        showKPIError();
    }
}

function updateKPIElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        const skeleton = element.querySelector('.skeleton-loader');
        if (skeleton) {
            skeleton.style.opacity = '0';
            setTimeout(() => {
                element.innerHTML = value;
                element.style.opacity = '1';
            }, 150);
        } else {
            element.innerHTML = value;
        }
    }
}

function showKPIError() {
    const kpiElements = ['today-revenue', 'today-orders', 'active-carts-count', 'active-carts-total'];
    kpiElements.forEach(id => {
        updateKPIElement(id, '<span class="text-red-500 text-sm">• Hata</span>');
    });
}

async function loadCharts() {
    await Promise.allSettled([
        loadSalesTrend(),
        loadOrderStatus(),
        loadTopProducts()
    ]);
}

async function loadSalesTrend() {
    try {
        const response = await fetchWithRetry('/api/admin/metrics/sales-trend?days=30');
        const data = await response.json();
        
        if (!data.series || data.series.length === 0) {
            showEmptyState('sales-trend-chart', 'Henüz satış verisi bulunmuyor');
            return;
        }
        
        const ctx = document.getElementById('sales-trend-chart');
        if (!ctx) return;
        
        if (charts.salesTrend) {
            charts.salesTrend.destroy();
        }
        
        charts.salesTrend = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.series.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Ciro (₺)',
                    data: data.series.map(item => item.revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: window.innerWidth < 768 ? 'bottom' : 'top',
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: window.innerWidth >= 768,
                            text: 'Tarih'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: window.innerWidth >= 768,
                            text: 'Ciro (₺)'
                        }
                    }
                }
            }
        });
        
    } catch (error) {
        console.error('Error loading sales trend:', error);
        showChartError('sales-trend-chart', 'Satış trendi yüklenirken hata oluştu');
    }
}

async function loadOrderStatus() {
    try {
        const response = await fetchWithRetry('/api/admin/metrics/order-status?days=30');
        const data = await response.json();
        
        const statusLabels = {
            'pending': 'Ödeme Bekliyor',
            'processing': 'Hazırlanıyor',
            'shipped': 'Kargoda',
            'delivered': 'Teslim Edildi'
        };
        
        const ctx = document.getElementById('order-status-chart');
        if (!ctx) return;
        
        if (charts.orderStatus) {
            charts.orderStatus.destroy();
        }
        
        charts.orderStatus = new Chart(ctx.getContext('2d'), {
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: window.innerWidth < 768 ? 'bottom' : 'right',
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading order status:', error);
        showChartError('order-status-chart', 'Sipariş durumu yüklenirken hata oluştu');
    }
}

async function loadTopProducts() {
    try {
        const response = await fetchWithRetry('/api/admin/metrics/top-products?days=30&limit=10');
        const data = await response.json();
        
        const tableBody = document.getElementById('top-products-table');
        if (!tableBody) return;
        
        if (!data.data || data.data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            Henüz ürün satışı bulunmuyor
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tableBody.innerHTML = data.data.map(product => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.product}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.sku}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.units}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₺${product.revenue.toLocaleString('tr-TR', {minimumFractionDigits: 2})}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading top products:', error);
        const tableBody = document.getElementById('top-products-table');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-red-500">
                        Ürün verileri yüklenirken hata oluştu
                        <button onclick="loadTopProducts()" class="ml-2 text-blue-600 hover:underline">Tekrar dene</button>
                    </td>
                </tr>
            `;
        }
    }
}

async function loadCronStatus() {
    try {
        const response = await fetchWithRetry('/api/admin/metrics/cron-summary');
        const data = await response.json();
        
        document.getElementById('cron-last-runs').innerHTML = data.last_runs.map(run => `
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded mb-2 hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium">${run.task}</span>
                <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">${run.status}</span>
            </div>
        `).join('');
        
        document.getElementById('cron-next-runs').innerHTML = data.next_runs.map(run => `
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded mb-2 hover:bg-gray-100 transition-colors">
                <span class="text-sm font-medium">${run.task}</span>
                <span class="text-xs text-gray-500">${new Date(run.due_at).toLocaleString('tr-TR')}</span>
            </div>
        `).join('');
        
        const failingElement = document.getElementById('cron-failing');
        if (data.failing && data.failing.length > 0) {
            failingElement.innerHTML = data.failing.map(fail => `
                <div class="p-2 bg-red-50 rounded mb-2 border border-red-200">
                    <div class="text-sm font-medium text-red-900">${fail.task}</div>
                    <div class="text-xs text-red-700 mt-1">${fail.last_error}</div>
                </div>
            `).join('');
        } else {
            failingElement.innerHTML = `
                <div class="text-center py-4">
                    <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-sm text-gray-500">Tüm işler başarılı</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading cron status:', error);
        document.getElementById('cron-failing').innerHTML = `
            <div class="text-center py-4 text-red-500">
                <p class="text-sm">Cron durumu yüklenirken hata oluştu</p>
                <button onclick="loadCronStatus()" class="mt-2 text-blue-600 hover:underline text-sm">Tekrar dene</button>
            </div>
        `;
    }
}
</script>
@endpush