<script>
import { h, defineComponent } from 'vue'

export default defineComponent({
  name: 'BaseButtons',
  props: {
    noWrap: Boolean,
    type: {
      type: String,
      default: 'justify-start',
    },
    classAddon: {
      type: String,
      default: '',
    },
    mb: {
      type: String,
      default: '-mb-3',
    },
  },
  render() {
    const hasSlot = this.$slots && this.$slots.default

    const parentClass = [
      'flex',
      'items-center',
      this.type,
      this.noWrap ? 'flex-nowrap' : 'flex-wrap',
      'gap-1.5 sm:gap-2',
    ]

    if (this.mb) {
      parentClass.push(this.mb)
    }

    return h(
      'div',
      { class: parentClass },
      hasSlot
        ? this.$slots.default().map((element) => {
            if (element && element.children && typeof element.children === 'object') {
              return h(
                element,
                {},
                element.children.map((child) => {
                  return h(child, { class: [this.classAddon] })
                }),
              )
            }

            return h(element, { class: [this.classAddon] })
          })
        : null,
    )
  },
})
</script>
