<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="">
    <div class="container mt-5">
        <div class="row">
            @if (session() && session()->has('success'))
                <div class="alert alert-dismissible fade show 
                @if (session()->get('success')) alert-success @else alert-danger @endif"
                    role="alert">
                    {{ session()->get('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="spinner-grow text-dark mx-auto spinner my-5" role="status" style="display:none">
                <span class="visually-hidden">Loading...</span>
            </div>
            <form method="POST" action="{{ url('/upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="input-group input-group-lg">
                    <input type="file" class="form-control " name="files[]" id="inputGroupFile04" multiple
                        aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                    <button class="btn btn-outline-dark" type="submit" id="inputGroupFileAddon04">
                        <span>Submit</span>
                    </button>
                </div>
            </form>
        </div>
        @if ($attachments && sizeof($attachments) > 0)
            <div class="row mt-5">
                <div class="form-group">
                    <a href="{{ url('delete-file/All') }}" class="btn btn-outline-danger">Delete All</a>
                    <a href="{{ url('download-file/All') }}" class="btn btn-outline-info">Download All</a>
                </div>
            </div>
        @endif
        <div class="row mt-5">
            @foreach ($attachments as $item)
                <div class="col-lg-3">
                    <div class="card" style="width: 18rem;">
                        <img src="data:image/png;base64,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"
                            style="width:100%;height:40%" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->name }}</h5>
                            <a href="{{ url('download-file/' . $item->hashname) }}" class="btn btn-primary">Download</a>
                            <a href="{{ url('delete-file/' . $item->id) }}" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script>
    $('#inputGroupFileAddon04').click(function() {
        $('.spinner').show()
    })
    $('.alert').fadeOut(5000)
</script>

</html>
