<div class="w-fit flex ml-4 items-center" x-data="{
    path: '{{ $path }}',
    value: '{{ $value }}',

    async save() {
        try {
            await axios.patch('{{ route('dashboard.update.value.single') }}', {
                path: this.path,
                value: this.value
            })
        } catch (error) {
            console.error('Failed to save:', error)
        }
    }
}">
    <span class ="text-white text-xl px-1">:</span>
    <input type="text" :id="path" x-model="value" class="bg-gray-800 text-white border border-default-medium text-xs rounded focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" />
    <button x-on:click="save()"
        class="ml-2 bg-success text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-body leading-5 rounded text-xs px-3 py-1.5 focus:outline-none">Save</button>
</div>
