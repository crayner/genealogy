'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import OpenFormSection from "./OpenFormSection";
import {DarkGreenA, DarkGreenP} from "../component/StyledCSS";

export default function RenderCategoryParents(props) {
    const {
        translations,
        parents,
        handleOpenForm
    } = props;
    
    function renderParents()
    {
        if (parents.length === 0) {
            return (<DarkGreenP>{translations.Categories}: {translations.noParentCategories} <OpenFormSection
                sectionName={'parents'} translations={translations} handleOpenForm={handleOpenForm}/></DarkGreenP>)
        }

        const result = Object.keys(parents).map(i => {
            i = Number(i);
            const parent = parents[i];
            if (parents.length - 1 === i) {
                return (<DarkGreenA key={i} href={parent.path}>{parent.name}</DarkGreenA>)
            }
            return (<Fragment key={i}><DarkGreenA href={parent.path}>{parent.name}</DarkGreenA> | </Fragment>)
        })
        return (<DarkGreenP>{translations.Categories}: {result} <OpenFormSection sectionName={'parents'}
                                                                                 translations={translations}
                                                                                 handleOpenForm={handleOpenForm}/></DarkGreenP>)
    }

    return (
        <Fragment>{renderParents()}</Fragment>
    );

}

RenderCategoryParents.propTypes = {
    translations: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
    parents: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array,
    ]).isRequired,
};