 - Object templates for reoccouring value objects

    <rdm:object-template
        name="millimeter"
        class="..."
        factory="..."
        serialize="..."
    >
        ...
    </rdm:object-template>

    <rdm:object template="millimeter" field="thickness" column="right_thickness" />

 - column-prefixes for import

    <rdm:import path="..." column-prefix="right_" />
    <rdm:import path="..." column-prefix="left_" />
