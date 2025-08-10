
@extends('admin.layouts.app')

@section('title', 'إدارة المحتوى')

@section('content')
<div class="row">
    {{-- Content Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي الصفحات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($contentStatistics['data']['pages']['total_pages']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي المقالات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($contentStatistics['data']['posts']['total_posts']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">إجمالي الفئات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($contentStatistics['data']['categories']['total_categories']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tags fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">إجمالي القوائم</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($contentStatistics['data']['menus']['total_menus']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recent Posts --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">المقالات الأخيرة</h6>
                <div>
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء مقال جديد
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>الفئات</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPosts as $post)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $post->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($post->excerpt, 100) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($post->categories->count() > 0)
                                        @foreach($post->categories->take(2) as $category)
                                            <span class="badge bg-primary me-1">{{ $category->name }}</span>
                                        @endforeach
                                        @if($post->categories->count() > 2)
                                            <span class="text-muted">+{{ $post->categories->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">لا توجد فئات</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $post->status === 'published' ? 'success' : 'secondary' }}">
                                        {{ $post->status === 'published' ? 'منشور' : 'مسودة' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $post->published_at ? $post->published_at->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($post->status === 'draft')
                                            <a href="{{ route('admin.posts.publish', $post->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المقال؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

    {{-- Popular Posts --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المقالات الأكثر زيارة</h6>
            </div>
            <div class="card-body">
                @foreach($popularPosts as $post)
                <div class="d-flex align-items-center mb-3">
                    @if($post->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ Str::limit($post->title, 30) }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted text-sm">{{ $post->published_at->format('d/m/Y') }}</span>
                            <span class="badge bg-primary">
                                <i class="fas fa-eye"></i> {{ number_format($post->views) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Popular Tags --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">العلامات الشائعة</h6>
            </div>
            <div class="card-body">
                @foreach($popularTags as $tag)
                <a href="{{ route('admin.posts.index', ['tag' => $tag->id]) }}" class="badge bg-primary text-white me-2 mb-2">
                    {{ $tag->name }}
                    <span class="badge bg-light text-dark ms-1">{{ $tag->posts_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Pages --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">الصفحات</h6>
                <div>
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء صفحة جديدة
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>الحالة</th>
                                <th>الزيارات</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPages as $page)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $page->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $page->slug }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $page->status === 'published' ? 'success' : 'secondary' }}">
                                        {{ $page->status === 'published' ? 'منشور' : 'مسودة' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="fas fa-eye"></i> {{ number_format($page->views) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $page->published_at ? $page->published_at->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.pages.show', $page->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($page->status === 'draft')
                                            <a href="{{ route('admin.pages.publish', $page->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الصفحة؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

    {{-- Menus --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">القوائم</h6>
                <div>
                    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء قائمة جديدة
                    </a>
                </div>
            </div>
            <div class="card-body">
                @foreach($menus as $menu)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">{{ $menu->name }}</h6>
                        <span class="badge bg-{{ $menu->status === 'active' ? 'success' : 'secondary' }}">
                            {{ $menu->status === 'active' ? 'نشط' : 'غير نشط' }}
                        </span>
                    </div>
                    <p class="text-muted mb-2">{{ $menu->description }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted text-sm">
                            <i class="fas fa-list"></i> {{ $menu->items()->count() }} عنصر
                        </span>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.menus.show', $menu->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه القائمة؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTables
        $('#reviewsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
            },
            order: [[4, 'desc']]
        });
    });
</script>
@endsection
