@extends('admin.layouts.app')

@section('title', 'Vergi Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vergi Yönetimi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Anasayfa</a></li>
                        <li class="breadcrumb-item active">Vergi Yönetimi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Toplam Vergi Sınıfı</p>
                            <h4 class="mb-2">{{ $stats['total_classes'] }}</h4>
                            <p class="text-white-50 mb-0"><span class="text-success me-2">{{ $stats['active_classes'] }} Aktif</span></p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-circle font-size-24">
                                <i class="bx bx-purchase-tag-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Toplam Vergi Oranı</p>
                            <h4 class="mb-2">{{ $stats['total_rates'] }}</h4>
                            <p class="text-white-50 mb-0"><span class="text-success me-2">{{ $stats['active_rates'] }} Aktif</span></p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-circle font-size-24">
                                <i class="bx bx-line-chart"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Toplam Vergi Kuralı</p>
                            <h4 class="mb-2">{{ $stats['total_rules'] }}</h4>
                            <p class="text-white-50 mb-0"><span class="text-success me-2">{{ $stats['active_rules'] }} Aktif</span></p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-circle font-size-24">
                                <i class="bx bx-rule"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Ortalama Vergi Oranı</p>
                            <h4 class="mb-2">
                                @if($taxRates->count() > 0)
                                    {{ number_format($taxRates->avg('rate') * 100, 2) }}%
                                @else
                                    0%
                                @endif
                            </h4>
                            <p class="text-white-50 mb-0"><span class="text-success me-2">Ortalama</span></p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-circle font-size-24">
                                <i class="bx bx-calculator"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Vergi Sınıfları</h4>
                        <a href="{{ route('admin.tax.classes.index') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus"></i> Tümünü Gör
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad</th>
                                    <th>Kod</th>
                                    <th>Varsayılan Oran</th>
                                    <th>Durum</th>
                                    <th>Ürün Sayısı</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxClasses as $taxClass)
                                <tr>
                                    <td>{{ $taxClass->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $taxClass->code }}</span></td>
                                    <td>{{ number_format($taxClass->default_rate * 100, 2) }}%</td>
                                    <td>
                                        @if($taxClass->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>{{ $taxClass->products_count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Vergi Oranları</h4>
                        <a href="{{ route('admin.tax.rates.index') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus"></i> Tümünü Gör
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad</th>
                                    <th>Kod</th>
                                    <th>Oran</th>
                                    <th>Sınıf</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxRates as $taxRate)
                                <tr>
                                    <td>{{ $taxRate->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $taxRate->code }}</span></td>
                                    <td>
                                        @if($taxRate->type === 'percentage')
                                            {{ number_format($taxRate->rate * 100, 2) }}%
                                        @else
                                            ₺{{ number_format($taxRate->rate, 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $taxRate->taxClass->name ?? '-' }}</td>
                                    <td>
                                        @if($taxRate->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Vergi Hesaplama Testi</h4>
                    <form id="taxTestForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="testAmount" class="form-label">Tutar (₺)</label>
                                    <input type="number" class="form-control" id="testAmount" name="amount" step="0.01" min="0" value="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="testCountry" class="form-label">Ülke</label>
                                    <select class="form-control" id="testCountry" name="country_code">
                                        <option value="TR" selected>Türkiye</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="testEntityType" class="form-label">Varlık Türü</label>
                                    <select class="form-control" id="testEntityType" name="entity_type">
                                        <option value="product">Ürün</option>
                                        <option value="shipping">Kargo</option>
                                        <option value="payment">Ödeme</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Hesapla</button>
                    </form>
                    <div id="taxTestResult" class="mt-4" style="display: none;">
                        <h5>Sonuçlar:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Temel Tutar</th>
                                    <td id="baseAmount">₺0.00</td>
                                </tr>
                                <tr>
                                    <th>Vergi Tutarı</th>
                                    <td id="taxAmount">₺0.00</td>
                                </tr>
                                <tr>
                                    <th>Toplam Tutar</th>
                                    <td id="totalAmount">₺0.00</td>
                                </tr>
                                <tr>
                                    <th>Etkin Vergi Oranı</th>
                                    <td id="effectiveRate">0%</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#taxTestForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('admin.tax.test-calculation') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#baseAmount').text('₺' + response.data.calculation.base_amount.toFixed(2));
                    $('#taxAmount').text('₺' + response.data.calculation.tax_amount.toFixed(2));
                    $('#totalAmount').text('₺' + response.data.calculation.total_with_tax.toFixed(2));
                    $('#effectiveRate').text((response.data.calculation.effective_rate * 100).toFixed(2) + '%');
                    $('#taxTestResult').show();
                } else {
                    alert('Hesaplama başarısız: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Hata oluştu: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endsection