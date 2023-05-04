'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import {Border, DarkGreenP, FlexboxContainer, H3, Main, MainContainer, Sidebar, Theme} from "../component/StyledCSS";
import BirthDetails from "./BirthDetails";
import FullName from "./FullName";
import ParentDetails from "./ParentDetails";
import SiblingDetails from "./SiblingDetails";
import SpouseDetails from "./SpouseDetails";
import ChildrenDetails from "./ChildrenDetails";
import DeathDetails from "./DeathDetails";
import SearchNameApp from "./SearchNameApp";

export default function IndividualDetails(props) {
    const {
        translations,
        individual,
        handleOpenForm,
        search,
    } = props;

    console.log(individual, translations);
    return (<Theme>
                <FlexboxContainer>
                    <Border />
                    <Main>
                        <MainContainer>
                            <FullName translations={translations} details={individual.full_name} handleOpenForm={handleOpenForm} format={'h3'} />
                            <BirthDetails translations={translations} details={individual.birth_details} handleOpenForm={handleOpenForm} />
                            <ParentDetails translations={translations} details={individual.parents} handleOpenForm={handleOpenForm} />
                            <SiblingDetails translations={translations} details={individual.siblings} handleOpenForm={handleOpenForm} />
                            <SpouseDetails translations={translations} details={individual.spouses} handleOpenForm={handleOpenForm} />
                            <ChildrenDetails translations={translations} details={individual.children} handleOpenForm={handleOpenForm} />
                            <DeathDetails translations={translations} details={individual.death_details} handleOpenForm={handleOpenForm} />
                        </MainContainer>
                    </Main>
                    <Sidebar>
                        <SearchNameApp search={search} translations={translations} />
                    </Sidebar>
                    <Border />
                </FlexboxContainer>
            </Theme>);
}

IndividualDetails.propTypes = {
    translations: PropTypes.object.isRequired,
    individual: PropTypes.object.isRequired,
    search: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};