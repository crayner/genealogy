'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenBold, DarkGreenP, FontWeightNormal, H3} from "../component/StyledCSS";

export default function FullName(props) {
    const {
        translations,
        details,
        handleOpenForm,
        format,
    } = props;

    function parseDetails() {
        let result = translations.full_name;
        result = result.split('|');
        result = result.map((value, i) => {
            if (value === '{formerly}') {
                return (<FontWeightNormal key={i}>{translations.formerly}</FontWeightNormal>);
            }
            if (value === '{used}') {
                return (<FontWeightNormal key={i}>{translations.aka}</FontWeightNormal>);
            }
            return (<Fragment key={i}>{value}</Fragment>)
        })
        if (format === 'h3') {
            return (<H3>{result}</H3>)
        }
        return (<DarkGreenP><DarkGreenBold>{result}</DarkGreenBold></DarkGreenP>);
    }


    return parseDetails();
}

FullName.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
    format: PropTypes.string,
};
FullName.defaultTypes = {
    format: 'strong',
}