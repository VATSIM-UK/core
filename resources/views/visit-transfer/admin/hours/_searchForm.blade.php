<div class="box-body">
    <div class="row">
        <div class="col-md-12">
            <p>Here, you can review the controlling hours completed by visiting members within a given
                date range. This will allow you to ensure that they are compliant with the rules
                stipulated in the Visiting & Transferring Policy. If the row is displayed in red, it
                means that the member has controlled more than 49% of their hours for that specific date
                range in the UK, rather than in their home division.</p>
        </div>
    </div>
    <form action="{{ route('adm.visiting.hours.search') }}" method="GET" autocomplete="off">
        @csrf
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="startDate" class="control-label">Start Date</label>
                <input type="text" name="startDate" id="startDate" value="{{ isset($startDate) ? $startDate : '' }}"
                       class="form-control">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="endDate" class="control-label">End Date</label>
                <input type="text" name="endDate" id="endDate" value="{{ isset($endDate) ? $endDate : '' }}"
                       class="form-control">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </div>

    </form>
</div>
