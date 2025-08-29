@extends('admin.layouts.app')

@section('title', 'Vergi Sınıfları')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vergi Sınıfları</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Anasayfa</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tax.index') }}">Vergi Yönetimi</a></li>
                        <li class="breadcrumb-item active">Vergi Sınıfları</li>
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
                        <h4 class="card-title">Vergi Sınıfları</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaxClassModal">
                            <i class="bx bx-plus"></i> Yeni Vergi Sınıfı
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad</th>
                                    <th>Kod</th>
                                    <th>Açıklama</th>
                                    <th>Varsayılan Oran</th>
                                    <th>Durum</th>
                                    <th>Vergi Oranı Sayısı</th>
                                    <th>Ürün Sayısı</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxClasses as $taxClass)
                                <tr>
                                    <td>{{ $taxClass->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $taxClass->code }}</span></td>
                                    <td>{{ $taxClass->description ?? '-' }}</td>
                                    <td>{{ number_format($taxClass->default_rate * 100, 2) }}%</td>
                                    <td>
                                        @if($taxClass->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>{{ $taxClass->tax_rates_count }}</td>
                                    <td>{{ $taxClass->products_count }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.tax.classes.edit', $taxClass->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-edit"></i> Düzenle
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-tax-class" data-id="{{ $taxClass->id }}">
                                                <i class="bx bx-trash"></i> Sil
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $taxClasses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Tax Class Modal -->
<div class="modal fade" id="createTaxClassModal" tabindex="-1" aria-labelledby="createTaxClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaxClassModalLabel">Yeni Vergi Sınıfı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaxClassForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ad *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Kod *</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="default_rate" class="form-label">Varsayılan Oran *</label>
                        <input type="number" class="form-control" id="default_rate" name="default_rate" step="0.0001" min="0" max="1" required>
                        <div class="form-text">0.00 ile 1.00 arasında bir değer girin (örn: 0.18 = %18)</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Aktif</label>
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
    // Create tax class
    $('#createTaxClassForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('admin.tax.classes.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#createTaxClassModal').modal('hide');
                    $('#createTaxClassForm')[0].reset();
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
    
    // Delete tax class
    $('.delete-tax-class').on('click', function() {
        if(confirm('Bu vergi sınıfını silmek istediğinizden emin misiniz?')) {
            let id = $(this).data('id');
            
            $.ajax({
                url: '/admin/tax/classes/' + id,
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