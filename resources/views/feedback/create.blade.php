@extends('layouts.app')

@section('title', 'Send Feedback')

@section('content')
<div class="page-header">
    <h1 class="mb-1">Send Feedback</h1>
    <p class="text-muted small">Tell us about any library session or your experience using the seat system.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-custom p-4">
            <form action="{{ route('feedback.store') }}" method="POST">
                @csrf
                <label for="rating" class="form-label fw-semibold">How would you rate your experience?</label>
                <select id="rating" name="rating" class="form-select mb-3" required>
                    <option value="">Choose a rating</option>
                    <option value="5" @selected(old('rating') == 5)>5 - Excellent</option>
                    <option value="4" @selected(old('rating') == 4)>4 - Good</option>
                    <option value="3" @selected(old('rating') == 3)>3 - Average</option>
                    <option value="2" @selected(old('rating') == 2)>2 - Poor</option>
                    <option value="1" @selected(old('rating') == 1)>1 - Very poor</option>
                </select>

                <label for="comment" class="form-label fw-semibold">Your feedback</label>
                <textarea id="comment" name="comment" class="form-control mb-3" rows="6" maxlength="1000" placeholder="Share your experience, suggestions, or any problems you faced." required>{{ old('comment') }}</textarea>

                <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-send me-2"></i>Submit Feedback</button>
            </form>
        </div>
    </div>
</div>
@endsection
