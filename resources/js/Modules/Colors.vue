<script>
const hexCharacters = 'a-f\\d';
const match3or4Hex = `#?[${hexCharacters}]{3}[${hexCharacters}]?`;
const match6or8Hex = `#?[${hexCharacters}]{6}([${hexCharacters}]{2})?`;
const nonHexChars = new RegExp(`[^#${hexCharacters}]`, 'gi');
const validHexSize = new RegExp(`^${match3or4Hex}$|^${match6or8Hex}$`, 'i');
export default {
    methods: {
        hx_rgb(hex, a = false){
          return this.toStringRgba(this.hexToRgbA(hex), a)
        },
        toStringRgba(rgba, a = false){
          return 'rgba('+rgba.r+','+rgba.g+','+rgba.b+','+(a === false ? rgba.a: a)+')'
        },
       hexToRgbA(hex){
        if (typeof hex !== 'string' || nonHexChars.test(hex) || !validHexSize.test(hex)) {
          return '#ccc';
        }
        
        hex = hex.replace(/^#/, '');
        let a = 1;

        if (hex.length === 8) {
          a = parseInt(hex.slice(6, 8), 16) / 255;
          hex = hex.slice(0, 6);
        }

        if (hex.length === 4) {
          a = parseInt(hex.slice(3, 4).repeat(2), 16) / 255;
          hex = hex.slice(0, 3);
        }

        if (hex.length === 3) {
          hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }

        const num = parseInt(hex, 16);
        const r = num >> 16;
        const g = (num >> 8) & 255;
        const b = num & 255;

        return {r, g, b, a};
      }

    }
};
</script>