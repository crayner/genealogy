'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import { DarkGreenOL, DarkGreenLI, DarkGreenA, DarkGreenBold } from "../component/StyledCSS";

export default function SpouseDetails(props) {
    const {
        translations,
        details,
        handleOpenForm,
    } = props;

    function parseDetails() {
        return details.map((spouse,i) => {
            let text = translations.spouses[i].name.split('|');
            text = text.map((value,index) => {
                if (value === '{date}') {
                    return spouse.details.date;
                }
                if (value === '{name}') {
                    return (<DarkGreenBold key={index}><DarkGreenA href={'/genealogy/individual/' + spouse.details.spouse_id + '/modify'}>{spouse.details.name}</DarkGreenA></DarkGreenBold>);
                }
                return value;
            })
            text.push(translations.spouses[i].location);
            return (<DarkGreenLI key={i}>{text}</DarkGreenLI>);
        })
    }

    return (<DarkGreenOL>{parseDetails()}</DarkGreenOL>);
}

SpouseDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.array.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};