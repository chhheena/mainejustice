<template>
    <div class="joms-postbox-loading">
        <img :src="img" alt="loader">
    </div>
</template>

<script>
export default {
    data() {
        const assetUrl = Joomla.getOptions('com_community').assets_url;
        return {
            img: assetUrl + 'ajax-loader.gif'
        }
    }
}
</script>
