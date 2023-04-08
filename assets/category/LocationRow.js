'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";

export default function LocationRow(props) {
    const {
        translations,
        category,
        handleOpenForm,
    } = props;

    if (typeof category.location === 'string' && category.location === '') return null;

    return (
        <Style.FlexContainer>
            <Style.Column2 />
            <Style.Column2 className={'withBorder'}><Style.DarkGreenP>{translations.location}:</Style.DarkGreenP></Style.Column2>
            <Style.Column6 className={'withBorder'}><Style.DarkGreenP>{category.location} <span style={{float: 'right', paddingRight: '0.25rem'}}><OpenFormSection sectionName={'address'}
                                                                                                                                                                               translations={translations}
                                                                                                                                                                               handleOpenForm={handleOpenForm}
            /></span></Style.DarkGreenP></Style.Column6>
            <Style.Column2 />
        </Style.FlexContainer>
    );

}

LocationRow.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};