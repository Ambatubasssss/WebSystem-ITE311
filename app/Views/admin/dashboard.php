<div class="container mt-4">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?= esc(session('name')) ?>.</p>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card"><div class="card-body"><h5 class="card-title">Total Users</h5><p class="card-text">--</p></div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body"><h5 class="card-title">Total Courses</h5><p class="card-text">--</p></div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body"><h5 class="card-title">Recent Activity</h5><p class="card-text">--</p></div></div>
        </div>
    </div>
</div>


