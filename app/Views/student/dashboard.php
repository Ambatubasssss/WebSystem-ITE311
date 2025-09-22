<div class="container mt-4">
    <h2>Student Dashboard</h2>
    <p>Welcome, <?= esc(session('name')) ?>.</p>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card"><div class="card-body"><h5 class="card-title">Enrolled Courses</h5><p class="card-text">--</p></div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body"><h5 class="card-title">Upcoming Deadlines</h5><p class="card-text">--</p></div></div>
        </div>
    </div>
</div>


