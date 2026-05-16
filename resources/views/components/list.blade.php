<div class="w-fit flex flex-col ml-4 p-1" x-data="{
    path: '{{ $path }}',
    list: {{ Js::from($list) }},
    draggedIndex: null,

    onDragStart(index) {
        this.draggedIndex = index
    },

    onDragOver(event, index) {
        event.preventDefault()
        if (this.draggedIndex === null || this.draggedIndex === index) return

        const dragged = this.list.splice(this.draggedIndex, 1)[0]
        this.list.splice(index, 0, dragged)
        this.draggedIndex = index
    },

    onDragEnd() {
        this.draggedIndex = null
    },

    async save(index) {
        try {
            await axios.patch('{{ route('dashboard.update.value.list') }}', {
                path: this.path,
                value: index ? [this.list[index]] : this.list
            })
        } catch (error) {
            console.error('Failed to save:', error)
        }
    },

}">
    <template x-for="(value, index) in list" :key="index">
        <div class="flex items-center cursor-grab active:cursor-grabbing"
            :class="{ 'opacity-50': draggedIndex === index }" draggable="true" x-on:dragstart="onDragStart(index)"
            x-on:dragover="onDragOver($event, index)" x-on:dragend="onDragEnd()">
            {{-- drag handle --}}
            <span class="text-gray-400 px-1 cursor-grab">⠿</span>

            <span class="text-white text-sm px-1" x-text="index + ':'"></span>
            <input type="text" x-model="list[index].label"
                class="bg-gray-800 text-white border border-default-medium text-xs rounded focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" />
            <button x-on:click="save(index)"
                class="ml-2 bg-success text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-body leading-5 rounded text-xs px-3 py-1.5 focus:outline-none">@translatable_settings('save')</button>
        </div>
    </template>

    <button x-show="draggedIndex === null" x-on:click="save()"
        class="w-fit cursor-pointer mt-2 bg-amber-500 text-white text-xs rounded px-3 py-1.5">@translatable_settings('save_order', ['ar' => 'حفظ الترتيب', 'en' => 'Save Order'])</button>
</div>
