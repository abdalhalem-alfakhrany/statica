<div class="node-group">
    <div class="node-header ml-[{{ $indent * 16 }}px]" x-data="{
        locked: true,
        path: '{{ $path }}',
        value: '{{ $label }}',

        async save() {

            try {
                await axios.patch('{{ route('dashboard.update.label') }}', { path: this.path, value: this.value });

                this.locked = true
            } catch (error) {
                console.error('Failed to save:', error)
            }
        }
    }">
        <div class="w-fit gap-1 flex ">
            <input type="text" :class="{ 'border': !locked, 'text-white': !locked, 'text-gray-400': locked }"
                x-model="value" :readonly="locked" :disabled="locked"
                class="bg-gray-800 border border-default-medium text-xs rounded focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" />
            <button x-on:click="locked ? locked = false : save()" x-text="locked ? 'UnLock' : 'Save'"
                :class="{ 'bg-success': !locked, 'bg-warning': locked }"
                class="text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-body leading-5 rounded text-xs px-1 py-1.5 focus:outline-none"></button>
        </div>
    </div>
    <div class="node-children">{!! $content !!}</div>
</div>
