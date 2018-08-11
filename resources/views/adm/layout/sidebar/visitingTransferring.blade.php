@if($_account->hasChildPermission("adm/visittransfer"))
    @include("visit-transfer.admin._sidebar")
@endif