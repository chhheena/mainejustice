export default function(categories, parent, prefix) {
    function sortCategories(categories, parent, prefix) {
        if ( !categories || !categories.length )
            return [];

        parent || (parent = '0');
        prefix || (prefix = '');

        let options = [];
        for ( let i = 0, id, name; i < categories.length; i++ ) {
            if ( categories[i].parent === parent ) {
                id = categories[i].id;
                name = prefix + categories[i].name;
                options.push({ id: id, name: name });
                options = options.concat( sortCategories( categories, id, name + ' › ' ) );
            }
        }

        return options;
    }

    return sortCategories(categories, parent, prefix);
}