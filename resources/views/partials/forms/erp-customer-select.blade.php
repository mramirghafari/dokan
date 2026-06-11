<x-erp-remote-select
    entity="customers"
    :name="$name ?? 'customer_id'"
    :value="$value ?? null"
    :placeholder="$placeholder ?? 'انتخاب مشتری'"
    :class="$class ?? 'form-select select2 erp-remote-select'"
    :filters="$filters ?? []"
/>
