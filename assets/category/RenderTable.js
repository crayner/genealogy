'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";
import AddressRow from "./AddressRow";
import LocationRow from "./LocationRow";
import WebpageRows from "./WebpageRows";
import Coordinates from "./Coordinates";
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
        return (<Fragment><br />{translations.aka}: <Style.DarkGreenBold>{result}</Style.DarkGreenBold></Fragment>)
    }

    return (
        <Style.Container>
            <Style.FlexContainer>
                <Style.Column2 />
                <Style.Column2 className={'centre withBorderFirst'}><Style.DarkGreenCentreP><Style.DarkGreenBold>{translations.name}:</Style.DarkGreenBold></Style.DarkGreenCentreP></Style.Column2>
                <Style.Column6 className={'withBorderFirst'}><Style.DarkGreenCentreP className={'centre'}><Style.DarkGreenBold>{category.displayName}</Style.DarkGreenBold> <span style={{float: 'right', paddingRight: '0.5rem'}}><OpenFormSection sectionName={section}
                                                                                                                                                                      translations={translations}
                                                                                                                                                                      handleOpenForm={handleOpenForm}
                /></span>{aka()}</Style.DarkGreenCentreP></Style.Column6>
                <Style.Column2 />
            </Style.FlexContainer>
            <LocationRow translations={translations} category={category} handleOpenForm={handleOpenForm} />
            <AddressRow translations={translations} category={category} handleOpenForm={handleOpenForm} />
            <Coordinates translations={translations} category={category} handleOpenForm={handleOpenForm} />
            <WebpageRows translations={translations} category={category} handleOpenForm={handleOpenForm} />
        </Style.Container>
    );

}

RenderTable.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
    section: PropTypes.string.isRequired,
};