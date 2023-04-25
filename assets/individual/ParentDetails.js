'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenA, DarkGreenP} from "../component/StyledCSS";

export default function ParentDetails(props) {
    const {
        translations,
        details,
        handleOpenForm,
    } = props;

    function parseDetails() {
        let result = translations.birth_parents.split('|');
        return result.map((value,i) => {
            if (value === '{mother}') {
                return (<DarkGreenA key={i} href={'/genealogy/individual/' + details.mother + '/modify'}>{translations.mother_name}</DarkGreenA>);
            }
            if (value === '{father}') {
                return (<DarkGreenA key={i} href={'/genealogy/individual/' + details.father + '/modify'}>{translations.father_name}</DarkGreenA>);
            }
            return (<Fragment key={i}>{value}</Fragment>);
        })
    }

    return (<DarkGreenP>{parseDetails()}</DarkGreenP>);
}

ParentDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};