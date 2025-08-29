@extends('admin.layouts.app')

@section('title', 'Vergi Sınıfı Düzenle')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vergi Sınıfı Düzenle</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Anasayfa</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tax.index') }}">Vergi Yönetimi</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tax.classes.index') }}">Vergi Sınıfları</a></li>
                        <li class="breadcrumb-item active">{{ $taxClass->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="editTaxClassForm" action="{{ route('admin.tax.classes.update', $taxClass->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Ad *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $taxClass->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Kod *</label>
                                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $taxClass->code) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $taxClass->description) }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_rate" class="form-label">Varsayılan Oran *</label>
                                    <input type="number" class="form-control" id="default_rate" name="default_rate" step="0.0001" min="0" max="1" value="{{ old('default_rate', $taxClass->default_rate) }}" required>
                                    <div class="form-text">0.00 ile 1.00 arasında bir değer girin (örn: 0.18 = %18)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Durum</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', $taxClass->is_active) ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('is_active', $taxClass->is_active) ? '' : 'selected' }}>Pasif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.tax.classes.index') }}" class="btn btn-secondary">Geri</a>
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Vergi Oranları</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaxRateModal">
                            <i class="bx bx-plus"></i> Yeni Vergi Oranı
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad</th>
                                    <th>Kod</th>
                                    <th>Oran</th>
                                    <th>Ülke</th>
                                    <th>Bileşik</th>
                                    <th>Öncelik</th>
                                    <th>Etkin Tarih</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxClass->taxRates as $taxRate)
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
                                    <td>{{ $taxRate->country_code }}</td>
                                    <td>
                                        @if($taxRate->is_compound)
                                            <span class="badge bg-success">Evet</span>
                                        @else
                                            <span class="badge bg-secondary">Hayır</span>
                                        @endif
                                    </td>
                                    <td>{{ $taxRate->priority }}</td>
                                    <td>
                                        @if($taxRate->effective_from)
                                            {{ $taxRate->effective_from->format('d.m.Y') }}
                                        @endif
                                        @if($taxRate->effective_until)
                                            - {{ $taxRate->effective_until->format('d.m.Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($taxRate->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary edit-tax-rate" data-id="{{ $taxRate->id }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-tax-rate" data-id="{{ $taxRate->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Tax Rate Modal -->
<div class="modal fade" id="createTaxRateModal" tabindex="-1" aria-labelledby="createTaxRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaxRateModalLabel">Yeni Vergi Oranı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaxRateForm">
                <div class="modal-body">
                    <input type="hidden" name="tax_class_id" value="{{ $taxClass->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rate_name" class="form-label">Ad *</label>
                                <input type="text" class="form-control" id="rate_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rate_code" class="form-label">Kod *</label>
                                <input type="text" class="form-control" id="rate_code" name="code" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rate_value" class="form-label">Oran *</label>
                                <input type="number" class="form-control" id="rate_value" name="rate" step="0.000001" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rate_type" class="form-label">Tür *</label>
                                <select class="form-control" id="rate_type" name="type" required>
                                    <option value="percentage">Yüzde</option>
                                    <option value="fixed">Sabit Tutar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country_code" class="form-label">Ülke Kodu *</label>
                                <input type="text" class="form-control" id="country_code" name="country_code" value="TR" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="region" class="form-label">Bölge</label>
                                <input type="text" class="form-control" id="region" name="region">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Öncelik *</label>
                                <input type="number" class="form-control" id="priority" name="priority" min="0" max="100" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_compound" class="form-label">Bileşik Vergi</label>
                                <select class="form-control" id="is_compound" name="is_compound">
                                    <option value="0">Hayır</option>
                                    <option value="1">Evet</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="effective_from" class="form-label">Etkin Başlangıç</label>
                                <input type="date" class="form-control" id="effective_from" name="effective_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="effective_until" class="form-label">Etkin Bitiş</label>
                                <input type="date" class="form-control" id="effective_until" name="effective_until">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rate_is_active" name="is_active" checked>
                        <label class="form-check-label" for="rate_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Create tax rate
    $('#createTaxRateForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('admin.tax.rates.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#createTaxRateModal').modal('hide');
                    $('#createTaxRateForm')[0].reset();
                    location.reload();
                } else {
                    alert('Hata oluştu: ' + response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + '\n';
                    });
                    alert(errorMsg);
                } else {
                    alert('Hata oluştu: ' + xhr.responseJSON.message);
                }
            }
        });
    });
    
    // Delete tax rate
    $('.delete-tax-rate').on('click', function() {
        if(confirm('Bu vergi oranını silmek istediğinizden emin misiniz?')) {
            let id = $(this).data('id');
            
            $.ajax({
                url: '/admin/tax/rates/' + id,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Hata oluştu: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});
</script>
@endsection