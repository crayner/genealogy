'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import {DarkGreenBold, DarkGreenP} from "../component/StyledCSS";

export default function AlternateNames(props) {
    const {
        translations,
        category,
    } = props;

    if (!category.aka) return null;

    function getAlternates() {
        const alternates = category.aka.split('|');
        return alternates.map((name, i) => {
            if (i === 0 ){
                return (<DarkGreenP key={i}>{translations.alternatename}: <DarkGreenBold>{name}</DarkGreenBold></DarkGreenP>)
            }
            return (<DarkGreenP key={i}><DarkGreenBold>{name}</DarkGreenBold></DarkGreenP>)
        });
    }

    return getAlternates();
}

AlternateNames.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
};