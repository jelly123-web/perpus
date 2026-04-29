@extends('layouts.admin')

@section('title', 'Cari Buku dengan Gambar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-2 text-gray-800">Cari Buku dengan Gambar</h1>
            <p class="mb-4">Unggah gambar sampul buku untuk mencari buku di database.</p>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Unggah Gambar Sampul Buku</h6>
                </div>
                <div class="card-body">
                    <form id="image-upload-form" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="bookImage">Pilih Gambar Sampul Buku:</label>
                            <input type="file" class="form-control-file" id="bookImage" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary" id="upload-button">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            Cari Buku
                        </button>
                    </form>
                    <div id="upload-status" class="mt-3"></div>
                </div>
            </div>

            <div class="card shadow mb-4" id="search-results" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hasil Pencarian</h6>
                </div>
                <div class="card-body">
                    <div id="results-content">
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('image-upload-form');
        const uploadButton = document.getElementById('upload-button');
        const uploadButtonSpinner = uploadButton.querySelector('.spinner-border');
        const uploadStatus = document.getElementById('upload-status');
        const searchResultsCard = document.getElementById('search-results');
        const resultsContent = document.getElementById('results-content');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            uploadButton.disabled = true;
            uploadButtonSpinner.style.display = 'inline-block';
            uploadStatus.innerHTML = '<div class="alert alert-info">Mengunggah gambar dan memproses...</div>';
            searchResultsCard.style.display = 'none';
            resultsContent.innerHTML = '<p>Loading...</p>';

            axios.post('{{ route('admin.books.search-by-image') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(function(response) {
                    uploadStatus.innerHTML = '';
                    searchResultsCard.style.display = 'block';
                    displayResults(response.data);
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    uploadStatus.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan: ' + (error.response?.data?.message || error.message) + '</div>';
                    searchResultsCard.style.display = 'none';
                })
                .finally(function() {
                    uploadButton.disabled = false;
                    uploadButtonSpinner.style.display = 'none';
                });
        });

        function displayResults(data) {
            let html = '';

            if (data.status === 'success') {
                html += '<p class="text-success">' + data.message + '</p>';

                if (data.extracted_attributes) {
                    html += '<h6>Atribut yang Diekstraksi:</h6>';
                    html += '<ul>';
                    html += '<li><strong>ISBN:</strong> ' + (data.extracted_attributes.isbn || 'Tidak Ditemukan') + '</li>';
                    if (data.extracted_attributes.title_candidates && data.extracted_attributes.title_candidates.length > 0) {
                        html += '<li><strong>Calon Judul:</strong> ' + data.extracted_attributes.title_candidates.join(', ') + '</li>';
                    }
                    if (data.extracted_attributes.author_candidates && data.extracted_attributes.author_candidates.length > 0) {
                        html += '<li><strong>Calon Penulis:</strong> ' + data.extracted_attributes.author_candidates.join(', ') + '</li>';
                    }
                    html += '</ul>';
                }

                if (data.books && data.books.length > 0) {
                    html += '<h6>Buku Ditemukan:</h6>';
                    html += '<div class="row">';
                    data.books.forEach(function(book) {
                        html += `
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    ${book.cover_image_url ? `<img src="${book.cover_image_url}" class="card-img-top" alt="${book.title}" style="max-height: 200px; object-fit: contain;">` : `<div class="text-center p-3">Tidak Ada Sampul</div>`}
                                    <div class="card-body">
                                        <h5 class="card-title">${book.title}</h5>
                                        <p class="card-text"><strong>Penulis:</strong> ${book.author || 'N/A'}</p>
                                        <p class="card-text"><strong>ISBN:</strong> ${book.isbn || 'N/A'}</p>
                                        <p class="card-text"><strong>Kategori:</strong> ${book.category?.name || 'N/A'}</p>
                                        <a href="/admin/books/${book.id}/edit" class="btn btn-sm btn-info">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-warning">Tidak ada buku yang cocok ditemukan berdasarkan gambar.</div>';
                }
            } else {
                html += '<div class="alert alert-danger">Error: ' + (data.message || 'Terjadi kesalahan tidak dikenal.') + '</div>';
            }

            resultsContent.innerHTML = html;
        }
    });
</script>
@endpush
