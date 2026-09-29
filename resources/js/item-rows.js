/**
 * Baris item dinamis untuk form Purchase Order & Purchase Request.
 *
 * Setiap baris punya item_type; daftar item_id diambil dari master sesuai
 * tipe tersebut, jadi user tidak bisa mengetik id yang tidak ada.
 */
export function itemRows(catalogOptions) {
    return {
        options: catalogOptions,

        rows: [{ item_type: 'feed', item_id: '', qty: '', price: '' }],

        add() {
            this.rows.push({ item_type: 'feed', item_id: '', qty: '', price: '' });
        },

        remove(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
    };
}
