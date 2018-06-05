<li>
  <div class="{{ ($type->requires_value) ? "needs-values" : "" }}">
      <div class="box box-info">
          <div class="box-header">
              <h4 class="box-title" style="font-size:1.25em">
                <span class="type_name">{{ trans('feedback.type.'.$type->name) }}</span>
              </h4>
          </div>
          <div class="box-body">
              Makes this input:<br>
              {!! sprintf($type->code, "example", "", "example", "example", "") !!}
          </div>
      </div>
  </div>
</li>
