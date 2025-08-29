@extends('admin.layouts.app')

@section('title', 'Vergi Kuralları')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vergi Kuralları</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Anasayfa</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tax.index') }}">Vergi Yönetimi</a></li>
                        <li class="breadcrumb-item active">Vergi Kuralları</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Vergi Kuralları</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaxRuleModal">
                            <i class="bx bx-plus"></i> Yeni Vergi Kuralı
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Vergi Oranı</th>
                                    <th>Varlık Türü</th>
                                    <th>Varlık ID</th>
                                    <th>Ülke</th>
                                    <th>Bölge</th>
                                    <th>Posta Kodu</th>
                                    <th>Müşteri Türü</th>
                                    <th>Sipariş Tutarı</th>
                                    <th>Öncelik</th>
                                    <th>Durdurma</th>
                                    <th>Etkin Tarih</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxRules as $taxRule)
                                <tr>
                                    <td>{{ $taxRule->taxRate->name ?? '-' }}<br><small class="text-muted">{{ $taxRule->taxRate->taxClass->name ?? '' }}</small></td>
                                    <td>
                                        @if($taxRule->entity_type === 'product')
                                            <span class="badge bg-primary">Ürün</span>
                                        @elseif($taxRule->entity_type === 'category')
                                            <span class="badge bg-success">Kategori</span>
                                        @elseif($taxRule->entity_type === 'customer')
                                            <span class="badge bg-info">Müşteri</span>
                                        @elseif($taxRule->entity_type === 'shipping')
                                            <span class="badge bg-warning">Kargo</span>
                                        @elseif($taxRule->entity_type === 'payment')
                                            <span class="badge bg-danger">Ödeme</span>
                                        @endif
                                    </td>
                                    <td>{{ $taxRule->entity_id ?? '-' }}</td>
                                    <td>{{ $taxRule->country_code }}</td>
                                    <td>{{ $taxRule->region ?? '-' }}</td>
                                    <td>
                                        @if($taxRule->postal_code_from || $taxRule->postal_code_to)
                                            {{ $taxRule->postal_code_from ?? '' }} - {{ $taxRule->postal_code_to ?? '' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($taxRule->customer_type === 'individual')
                                            <span class="badge bg-info">Bireysel</span>
                                        @elseif($taxRule->customer_type === 'company')
                                            <span class="badge bg-success">Kurumsal</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($taxRule->order_amount_from !== null || $taxRule->order_amount_to !== null)
                                            ₺{{ number_format($taxRule->order_amount_from ?? 0, 2) }} - ₺{{ number_format($taxRule->order_amount_to ?? 0, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $taxRule->priority }}</td>
                                    <td>
                                        @if($taxRule->stop_processing)
                                            <span class="badge bg-danger">Evet</span>
                                        @else
                                            <span class="badge bg-secondary">Hayır</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($taxRule->date_from)
                                            {{ $taxRule->date_from->format('d.m.Y') }}
                                        @endif
                                        @if($taxRule->date_to)
                                            - {{ $taxRule->date_to->format('d.m.Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($taxRule->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary edit-tax-rule" data-id="{{ $taxRule->id }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-tax-rule" data-id="{{ $taxRule->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $taxRules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Tax Rule Modal -->
<div class="modal fade" id="createTaxRuleModal" tabindex="-1" aria-labelledby="createTaxRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaxRuleModalLabel">Yeni Vergi Kuralı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaxRuleForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax_rate_id" class="form-label">Vergi Oranı *</label>
                                <select class="form-control" id="tax_rate_id" name="tax_rate_id" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->taxClass->name ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entity_type" class="form-label">Varlık Türü *</label>
                                <select class="form-control" id="entity_type" name="entity_type" required>
                                    <option value="">Seçiniz</option>
                                    <option value="product">Ürün</option>
                                    <option value="category">Kategori</option>
                                    <option value="customer">Müşteri</option>
                                    <option value="shipping">Kargo</option>
                                    <option value="payment">Ödeme</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entity_id" class="form-label">Varlık ID</label>
                                <input type="number" class="form-control" id="entity_id" name="entity_id">
                                <div class="form-text">Belirli bir varlık için kural uygulamak istiyorsanız ID girin</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country_code" class="form-label">Ülke Kodu *</label>
                                <input type="text" class="form-control" id="country_code" name="country_code" value="TR" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="region" class="form-label">Bölge</label>
                                <input type="text" class="form-control" id="region" name="region">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_type" class="form-label">Müşteri Türü</label>
                                <select class="form-control" id="customer_type" name="customer_type">
                                    <option value="">Tümü</option>
                                    <option value="individual">Bireysel</option>
                                    <option value="company">Kurumsal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code_from" class="form-label">Posta Kodu (Başlangıç)</label>
                                <input type="text" class="form-control" id="postal_code_from" name="postal_code_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code_to" class="form-label">Posta Kodu (Bitiş)</label>
                                <input type="text" class="form-control" id="postal_code_to" name="postal_code_to">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="order_amount_from" class="form-label">Sipariş Tutarı (Başlangıç)</label>
                                <input type="number" class="form-control" id="order_amount_from" name="order_amount_from" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="order_amount_to" class="form-label">Sipariş Tutarı (Bitiş)</label>
                                <input type="number" class="form-control" id="order_amount_to" name="order_amount_to" step="0.01">
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
                                <label for="rule_is_active" class="form-label">Durum</label>
                                <select class="form-control" id="rule_is_active" name="is_active">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Etkin Başlangıç</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Etkin Bitiş</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="stop_processing" name="stop_processing">
                                <label class="form-check-label" for="stop_processing">İşlemeyi Durdur</label>
                                <div class="form-text">Bu kural uygulandığında diğer kurallar uygulanmaz</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>
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
    // Create tax rule
    $('#createTaxRuleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('admin.tax.rules.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#createTaxRuleModal').modal('hide');
                    $('#createTaxRuleForm')[0].reset();
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
    
    // Delete tax rule
    $('.delete-tax-rule').on('click', function() {
        if(confirm('Bu vergi kuralını silmek istediğinizden emin misiniz?')) {
            let id = $(this).data('id');
            
            $.ajax({
                url: '/admin/tax/rules/' + id,
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