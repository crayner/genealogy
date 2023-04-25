'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenA, DarkGreenP} from "../component/StyledCSS";

export default function ChildrenDetails(props) {
    const {
        translations,
        details,
        handleOpenForm,
    } = props;

    function parseDetails() {
        let result = [];
        if (details.length === 0) return null;
        result.push(translations.children_list);
        details.map((child, i) => {
            if (i === 0) {
                result.push(<DarkGreenA key={i} href={'/genealogy/individual/' + child.id + '/modify'}>{child.name}</DarkGreenA>);
                return;
            }
            if (i === (details.length - 1)) {
                result.push(<Fragment key={i}> {translations.and} <DarkGreenA key={i} href={'/genealogy/individual/' + child.id + '/modify'}>{child.name}</DarkGreenA></Fragment>);
                return;
            }
            result.push(<Fragment key={i}>, <DarkGreenA key={i} href={'/genealogy/individual/' + child.id + '/modify'}>{child.name}</DarkGreenA></Fragment>);
        })
        return result;
    }

    return (<DarkGreenP>{parseDetails()}</DarkGreenP>);
}

ChildrenDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    details: PropTypes.array.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};