<?php
///////////////////////////////////////////////////////////////////////////////
// does what html_entity_decode does, but allows for some characters to not be decoded
function decode_html(string $string = NULL, array $skip = []): ?string {
    if ( ! $string) {
        return $string;
    }

    // get the list with all characters and remove the skipped ones
    $list = get_html_translation_table(HTML_ENTITIES);
    foreach ($skip as $item) {
        unset($list[$item]);
    }

    $characters = array_keys($list);
    $entities   = array_values($list);

    return str_replace($entities, $characters, $string);
}
