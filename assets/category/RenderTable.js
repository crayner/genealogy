'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";
import {DarkGreenBold, DarkGreenCentreP, DarkGreenP, Theme} from "../component/StyledCSS";
const reactStringReplace = require('react-string-replace');

export default function RenderTable(props) {
    const {
        translations,
        category,
        handleOpenForm,
        section,
    } = props;

    function aka(){
        if (category.aka === null || category.aka.length <= 0) return null;
        const result = reactStringReplace(category['aka'], '|', <br />);
        return (<Fragment><br />{translations.aka}: <DarkGreenBold>{result}</DarkGreenBold></Fragment>)
    }

    function getLocation() {
        console.log(category.location, typeof category.location);
        if (typeof category.location === 'string' && category.location === '') return null;
        return (<Style.FlexContainer>
            <Style.Column2 />
            <Style.Column2 className={'withBorder'}><DarkGreenP>{translations.location}:</DarkGreenP></Style.Column2>
            <Style.Column6 className={'withBorder'}><DarkGreenP>{category.location}</DarkGreenP></Style.Column6>
            <Style.Column2 />
        </Style.FlexContainer>);
    }

    return (
        <Style.Container>
            <Style.FlexContainer>
                <Style.Column2 />
                <Style.Column2 className={'centre withBorderFirst'}><DarkGreenCentreP><DarkGreenBold>{translations.name}:</DarkGreenBold></DarkGreenCentreP></Style.Column2>
                <Style.Column6 className={'withBorderFirst'}><DarkGreenCentreP className={'centre'}><DarkGreenBold>{category.displayName}</DarkGreenBold> <span style={{float: 'right', paddingRight: '0.25rem'}}><OpenFormSection sectionName={section}
                                                                                                                                                                      translations={translations}
                                                                                                                                                                      handleOpenForm={handleOpenForm}
                /></span>{aka()}</DarkGreenCentreP></Style.Column6>
                <Style.Column2 />
            </Style.FlexContainer>
            {getLocation()}
        </Style.Container>
    );

}

RenderTable.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
    section: PropTypes.string.isRequired,
};