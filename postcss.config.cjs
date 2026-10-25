/**
 * postcss.config.js
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 */

module.exports = () => {
    return {
        plugins: {
            "@tailwindcss/postcss": {},
            // cssnano: {
            //     preset: require('cssnano-preset-advanced')
            // }
        }
    };
};

