'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import { DarkGreenP } from "../component/StyledCSS";

export default function DeathDetails(props) {
    const {
        translations,
        details,
        handleOpenForm,
    } = props;

    function parseDetails() {
        let result = translations.death_details_date + translations.death_details_location;
        result = result.replaceAll('{', '|{').replaceAll('}', '}|');
        result = result.split('|');
        return result.map((value, i) => {
            if (value === '{date}') {
                return (<strong key={i}>{details.date}</strong>);
            }
            if (value === '{location}') {
                return (<strong key={i}>{details.location}</strong>);
            }
            if (value === '{age}') {
                return details.age;
            }
            return (<Fragment key={i}>{value}</Fragment>)
        })
    }

    return (<DarkGreenP>{parseDetails()}</DarkGreenP>);
}

DeathDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};