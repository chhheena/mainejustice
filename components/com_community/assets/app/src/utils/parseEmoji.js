export default function() {
    const $ = window.jQuery;
    const selectors = [
        '.joms-stream__body [data-type=stream-content]',
        '.joms-js--comment-content',
        '.joms-stream__body .colorful-status__container'
    ];
    const $elms = $(selectors.join(','));
    
    $elms.each((idx, elm) => {
        window.twemoji.parse(elm);
    })
}