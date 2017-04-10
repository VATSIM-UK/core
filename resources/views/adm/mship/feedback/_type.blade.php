<li style="list-style: none">
  <div class="col-md-12 {{ ($type->requires_value) ? "needs-values" : "" }}">
      <div class="box box-warning">
          <div class="box-header">
              <h4 class="box-title" style="font-size:1em">
                <span class="type_name">{{ $type->name }}</span>
              </h4>
          </div>
          <div class="box-body">
              Makes this input:</br>
              {!! sprintf($type->code, "example", "", "example", "example", "") !!}
          </div>
      </div>
  </div>
</li>
