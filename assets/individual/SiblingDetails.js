'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenA, DarkGreenP} from "../component/StyledCSS";

export default function SiblingDetails(props) {
    const {
        translations,
        details,
        handleOpenForm,
    } = props;

    function parseDetails() {
        let result = [];
        result.push(<Fragment key={'sentence'}>{translations.sibling_sentence}</Fragment>);
        details.map((value, index) => {
            if (index === details.length - 1) {
                result.push(<Fragment key={index}> {translations.and} <DarkGreenA href={'/genealogy/individual/' + value + '/modify'}>{translations.siblings[index]}</DarkGreenA>.</Fragment>);
            } else if (index === 0) {
                result.push(<DarkGreenA key={index}
                                        href={'/genealogy/individual/' + value + '/modify'}>{translations.siblings[index]}</DarkGreenA>);
            } else {
                result.push(<Fragment key={index}>, <DarkGreenA href={'/genealogy/individual/' + value + '/modify'}>{translations.siblings[index]}</DarkGreenA></Fragment>);
            }
        })
        return result;
    }

    return (<DarkGreenP>{parseDetails()}</DarkGreenP>);
}

SiblingDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.array.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};