/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
const jchPlatform = (function () {

    let jch_ajax_url_optimizeimages = 'index.php?option=com_jchoptimize&view=OptimizeImage&task=optimizeimage';
    let jch_ajax_url_multiselect = 'index.php?option=com_jchoptimize&view=Ajax&task=multiselect';
    let jch_ajax_url_smartcombine = 'index.php?option=com_jchoptimize&view=Ajax&task=smartcombine';
    let jch_loader_image_url = '../media/com_jchoptimize/core/images/loader.gif';

    /**
     *
     * @param int
     * @param id
     */
    const applyAutoSettings = function (int, id) {
        const auto_settings = document.querySelectorAll("figure.icon.auto-setting");
        const wrappers = document.querySelectorAll("figure.icon.auto-setting span.toggle-wrapper");
        let image = document.createElement("img");
        image.src = jch_loader_image_url;

        for (const wrapper of wrappers) {
            wrapper.replaceChild(image.cloneNode(true), wrapper.firstChild);
        }

        let url = "index.php?option=com_jchoptimize&view=ApplyAutoSetting&autosetting=s" + int;

        postData(url)
            .then(data => {
                for (const auto_setting of auto_settings) {
                    auto_setting.className = "icon auto-setting disabled";
                }

                //if the response returned without error then the setting is applied
                if (data.success) {
                    const current_setting = document.getElementById(id);
                    current_setting.className = "icon auto-setting enabled";
                    const enable_combine = document.getElementById("combine-files-enable")
                    enable_combine.className = "icon enabled";
                }

                for (const wrapper of wrappers) {
                    let toggle = document.createElement("i");
                    toggle.className = "toggle fa";
                    wrapper.replaceChild(toggle, wrapper.firstChild);
                }
            })
    };

    /**
     *
     * @param setting
     * @param id
     */
    const toggleSetting = function (setting, id) {
        let figure = document.getElementById(id);
        let wrapper = document.querySelector("#" + id + " span.toggle-wrapper");
        let toggle = wrapper.firstChild;
        const image = document.createElement("img");
        image.src = jch_loader_image_url;
        wrapper.replaceChild(image, toggle);

        if (setting === 'combine_files_enable') {
            const auto_settings = document.querySelectorAll("figure.icon.auto-setting");
            for (const auto_setting of auto_settings) {
                auto_setting.className = "icon auto-setting disabled";
            }
        }

        let url = "index.php?option=com_jchoptimize&view=ToggleSetting&setting=" + setting;

        postData(url)
            .then(data => {
                figure.classList.remove("enabled", "disabled");
                figure.classList.add(data.class);

                if (id === 'optimize-css-delivery') {
                    let unused_css = document.getElementById("remove-unused-css");
                    unused_css.classList.remove("enabled", "disabled");
                    unused_css.classList.add(data.class2);
                }

                if (id === 'remove-unused-css') {
                    let optimize_css = document.getElementById("optimize-css-delivery");
                    optimize_css.classList.remove("enabled", 'disabled');
                    optimize_css.classList.add(data.class2);
                }

                if (setting === 'combine_files_enable') {
                    if (data.auto !== false) {
                        enabled_auto_setting = document.getElementById(data.auto);
                        enabled_auto_setting.classList.remove("disabled");
                        enabled_auto_setting.classList.add("enabled");
                    }
                }
                wrapper.replaceChild(toggle, image);
            })
    };

    const submitForm = function () {
        Joomla.submitbutton('config.save.component.apply');
    };

    async function postData(url) {
        const response = await fetch(url, {
            method: 'GET',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            redirect: 'follow',
            referrerPolicy: 'no-referrer',
        });

        return response.json();
    }

    return {
        jch_ajax_url_multiselect: jch_ajax_url_multiselect,
        jch_ajax_url_optimizeimages: jch_ajax_url_optimizeimages,
        jch_ajax_url_smartcombine: jch_ajax_url_smartcombine,
        applyAutoSettings: applyAutoSettings,
        toggleSetting: toggleSetting,
        submitForm: submitForm
    }

})();