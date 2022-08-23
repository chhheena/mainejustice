<template>

<div class="emoji-mart-search">
  <input type="text" :placeholder="i18n.search" v-model="value">
</div>

</template>

<script>

import NimbleEmojiIndex from '../utils/emoji-index/nimble-emoji-index'
import debounce from 'lodash/debounce';

export default {
  props: {
    data: {
      type: Object,
      required: true
    },
    i18n: {
      type: Object,
      required: true
    },
    maxResults: {
      type: Number,
      default: 75
    },
    autoFocus: {
      type: Boolean,
      default: false
    },
    emojisToShowFilter: {
      type: Function
    },
    include: {
      type: Array
    },
    exclude: {
      type: Array
    },
    custom: {
      type: Array
    }
  },
  data() {
    return {
      value: ''
    }
  },
  computed: {
    emojiIndex() {
      return new NimbleEmojiIndex(this.data)
    }
  },
  watch: {
    value() {
      this.search();
    }
  },
  mounted() {
    let $input = this.$el.querySelector('input')

    if (this.autoFocus) {
      $input.focus()
    }
  },

  methods: {
    search: debounce(function() {
      let emojis = this.emojiIndex.search(this.value, {
        emojisToShowFilter: this.emojisToShowFilter,
        maxResults: this.maxResults,
        include: this.include,
        exclude: this.exclude,
        custom: this.custom
      })

      this.$emit('search', emojis)
    }, 300),

    clear() {
      this.value = ''
    },
  }
}

</script>

<style>

.emoji-mart-search {
  margin-top: 6px;
  padding: 0 6px;
}

.emoji-mart-search input {
  height: 30px;
  font-size: 16px;
  display: block;
  width: 100%;
  padding: .2em .6em;
  border-radius: 25px;
  border: 1px solid #d9d9d9;
  outline: 0;
}

</style>
