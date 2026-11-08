@extends('layouts.admin')

@section('title', 'Edit Listing - ' . $property->title)
@section('page_title', 'Edit Listing Properti & Unit')

@section('content')
@php
    $initialSuffix = old('title_suffix');
    if ($initialSuffix === null) {
        $parts = explode(' - ', $property->title, 2);
        $initialSuffix = count($parts) > 1 ? $parts[1] : '';
    }
@endphp

<div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 p-2.5 sm:p-6 shadow-xs"
     x-data="{
        selectedCityId: '{{ old('city_id', $property->city_id) }}',
        cityInputMode: '{{ old('custom_city') ? 'custom' : 'select' }}',
        customCity: '{{ old('custom_city', '') }}',
        citySearch: '',
        openCityList: false,
        selectedDistrictId: '{{ old('district_id', $property->district_id) }}',
        selectedProjectId: '{{ old('project_id', $property->project_id) }}',
        projectInputMode: '{{ old('custom_project_name') ? 'custom' : 'select' }}',
        customProjectName: '{{ old('custom_project_name', '') }}',
        projSearch: '',
        openProjList: false,
        propertyType: '{{ old('property_type', $property->property_type) }}',
        listingType: '{{ old('listing_type', $property->listing_type) }}',
        bedrooms: '{{ old('bedrooms', $property->bedrooms ?? '2') }}',
        maidBedrooms: '{{ old('maid_bedrooms', $property->maid_bedrooms ?? '') }}',
        bathrooms: '{{ old('bathrooms', $property->bathrooms ?? '1') }}',
        maidBathrooms: '{{ old('maid_bathrooms', $property->maid_bathrooms ?? '') }}',
        floors: '{{ old('floors', $property->floors ?? '1') }}',
        carports: '{{ old('carports', $property->carports ?? '1') }}',
        landArea: '{{ old('land_area', $property->land_area ?? '') }}',
        buildingArea: '{{ old('building_area', $property->building_area ?? '') }}',
        furnishedStatus: '{{ old('furnished_status', $property->furnished_status ?? 'unfurnished') }}',
        certificate: '{{ old('certificate', $property->certificate ?? 'SHM') }}',
        facing: '{{ old('facing', $property->facing ?? '') }}',
        roadWidth: '{{ old('road_width', $property->road_width ?? '') }}',
        electricity: '{{ old('electricity', $property->electricity ?? '2200') }}',
        price: '{{ old('price', $property->price ?? '') }}',
        formattedPrice: '',
        originalPrice: '{{ old('original_price', $property->original_price ?? '') }}',
        formattedOriginalPrice: '',
        monthlyRentPrice: '{{ old('monthly_rent_price', $property->monthly_rent_price ?? '') }}',
        formattedMonthlyRentPrice: '',
        customSuffix: '{{ addslashes($initialSuffix) }}',
        ownerType: '{{ old('owner_type', $property->owner_type ?? 'vendor') }}',
        codeSource: '{{ str_starts_with(strtoupper($property->property_code ?? ''), 'V') ? 'V' : 'A' }}',
        selectedAgentId: '{{ old('agent_id', $property->agent_id ?? ($currentAgent ? $currentAgent->id : '')) }}',
        agentsMap: @js($agents->pluck('initials', 'id')),
        agentsBranchMap: @js($agents->pluck('office_branch', 'id')),
        agentsNameMap: @js($agents->pluck('name', 'id')),
        nextSequences: @js($nextSequences ?? []),
        isAutoCode: false,
        propertyCode: '{{ old('property_code', $property->property_code ?? '') }}',
        isSubmitting: false,

        // Apartemen specific
        unitType: '{{ str_replace(' ', '', old('unit_type', $property->unit_type ?? '')) }}',
        unitTower: '{{ old('unit_tower', $property->unit_tower ?? '') }}',
        unitFloor: '{{ old('unit_floor', $property->unit_floor ?? '') }}',
        unitNumber: '{{ old('unit_number', $property->unit_number ?? '') }}',
        unitView: '{{ old('unit_view', $property->unit_view ?? '') }}',
        dimension: '{{ old('dimension', $property->dimension ?? '') }}',

        // Ruko, Commercial & Tanah specific
        areaUnit: 'm2',
        priceType: 'total', // 'total' or 'per_m2'
        pricePerM2Input: '',
        formattedPricePerM2: '',
        onPricePerM2Change() {
            const p = parseFloat(this.pricePerM2Input);
            const area = (this.propertyType === 'tanah' || this.propertyType === 'tanah_residensial' || this.propertyType === 'tanah_komersial')
                ? parseFloat(this.landArea)
                : (parseFloat(this.buildingArea) || parseFloat(this.landArea));
            if (p && area && area > 0) {
                const total = Math.round(p * area);
                this.price = String(total);
                this.formattedPrice = total.toLocaleString('id-ID');
            }
        },
        onPriceInput(e) {
            const raw = e.target.value.replace(/\D/g, '');
            this.price = raw;
            this.formattedPrice = raw ? Number(raw).toLocaleString('id-ID') : '';
        },
        onOriginalPriceInput(e) {
            const raw = e.target.value.replace(/\D/g, '');
            this.originalPrice = raw;
            this.formattedOriginalPrice = raw ? Number(raw).toLocaleString('id-ID') : '';
        },
        onMonthlyRentPriceInput(e) {
            const raw = e.target.value.replace(/\D/g, '');
            this.monthlyRentPrice = raw;
            this.formattedMonthlyRentPrice = raw ? Number(raw).toLocaleString('id-ID') : '';
        },
        onPricePerM2Input(e) {
            const raw = e.target.value.replace(/\D/g, '');
            this.pricePerM2Input = raw;
            this.formattedPricePerM2 = raw ? Number(raw).toLocaleString('id-ID') : '';
            this.onPricePerM2Change();
        },

        allCities: @js($cities),
        allProjects: @js($projects),
        existingImages: @js(!empty($property->images) && is_array($property->images) ? array_values($property->images) : []),
        newImagePreviews: [],
        defaultPhotos: {
            rumah: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            apartemen: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
            tanah: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            tanah_residensial: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            tanah_komersial: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            ruko: 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80',
            gudang: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80',
            pabrik: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80',
            hotel: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
            gedung: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            kantor: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            tempat_usaha: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            villa: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
            kost: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80'
        },

        getDefaultPhoto() {
            return this.defaultPhotos[this.propertyType] || this.defaultPhotos['rumah'];
        },

        handleFileSelect(e) {
            const files = Array.from(e.target.files || []);
            const imageFiles = files.filter(f => f.type.startsWith('image/'));
            imageFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.newImagePreviews.push({
                        file: file,
                        name: file.name,
                        url: event.target.result,
                        size: (file.size / 1024 / 1024).toFixed(2) + ' MB'
                    });
                    this.syncFilesInput();
                };
                reader.readAsDataURL(file);
            });
        },

        removePreview(idx) {
            this.newImagePreviews.splice(idx, 1);
            this.syncFilesInput();
        },

        setCover(idx) {
            if (idx === 0 || idx >= this.newImagePreviews.length) return;
            const item = this.newImagePreviews.splice(idx, 1)[0];
            this.newImagePreviews.unshift(item);
            this.syncFilesInput();
        },

        movePreview(idx, direction) {
            const targetIdx = idx + direction;
            if (targetIdx < 0 || targetIdx >= this.newImagePreviews.length) return;
            const item = this.newImagePreviews.splice(idx, 1)[0];
            this.newImagePreviews.splice(targetIdx, 0, item);
            this.syncFilesInput();
        },

        syncFilesInput() {
            const input = document.getElementById('image_files_input');
            if (!input) return;
            try {
                const dt = new DataTransfer();
                this.newImagePreviews.forEach(item => {
                    if (item.file) {
                        dt.items.add(item.file);
                    }
                });
                input.files = dt.files;
            } catch (err) {
                console.error('DataTransfer sync error:', err);
            }
        },

        clearFiles() {
            const input = document.getElementById('image_files_input');
            if (input) input.value = '';
            this.newImagePreviews = [];
        },

        removeExisting(idx) {
            this.existingImages.splice(idx, 1);
        },

        setCoverExisting(idx) {
            if (idx === 0 || idx >= this.existingImages.length) return;
            const item = this.existingImages.splice(idx, 1)[0];
            this.existingImages.unshift(item);
        },

        moveExisting(idx, direction) {
            const targetIdx = idx + direction;
            if (targetIdx < 0 || targetIdx >= this.existingImages.length) return;
            const item = this.existingImages.splice(idx, 1)[0];
            this.existingImages.splice(targetIdx, 0, item);
        },

        // Filter projects berdasarkan Kota DAN Tipe Properti secara otomatis
        get filteredProjects() {
            let list = this.allProjects;

            // 1. Filter by Kota
            if (this.selectedCityId) {
                list = list.filter(p => p.city_id == this.selectedCityId);
            }

            // 2. Filter by Tipe Properti
            if (this.propertyType) {
                const typeNorm = (this.propertyType || '').toLowerCase();
                list = list.filter(p => {
                    const pType = (p.type || '').toLowerCase();
                    if (!pType) return true;
                    if (typeNorm === 'rumah') {
                        return ['rumah', 'residensial', 'perumahan', 'cluster', 'house'].includes(pType);
                    } else if (typeNorm === 'apartemen') {
                        return ['apartemen', 'kondominium', 'apartment', 'condo'].includes(pType);
                    } else if (['ruko', 'kantor', 'tempat_usaha'].includes(typeNorm)) {
                        return ['ruko', 'komersial', 'shophouse', 'commercial', 'ruko_complex', 'kantor'].includes(pType);
                    } else if (['tanah', 'tanah_residensial', 'tanah_komersial'].includes(typeNorm)) {
                        return ['tanah', 'kavling', 'land'].includes(pType);
                    } else if (['gudang', 'pabrik'].includes(typeNorm)) {
                        return ['gudang', 'pergudangan', 'warehouse', 'industri', 'pabrik'].includes(pType);
                    } else if (['villa', 'kost'].includes(typeNorm)) {
                        return ['villa', 'resort', 'resor', 'kost'].includes(pType);
                    }
                    return pType === typeNorm;
                });
            }

            return list;
        },

        get projectDropdownLabel() {
            const map = {
                apartemen: 'Apartemen',
                rumah: 'Rumah / Cluster',
                ruko: 'Kompleks Ruko',
                kantor: 'Gedung Kantor',
                tempat_usaha: 'Kompleks Usaha',
                tanah: 'Kawasan / Kavling',
                tanah_residensial: 'Kawasan / Kavling',
                tanah_komersial: 'Kawasan Komersial',
                gudang: 'Kawasan Pergudangan',
                pabrik: 'Kawasan Pabrik',
                villa: 'Villa / Resort',
                hotel: 'Hotel',
                gedung: 'Gedung',
                kost: 'Kost'
            };
            return map[this.propertyType] || 'Cluster / Proyek';
        },

        get currentCityDistricts() {
            if (!this.selectedCityId) return [];
            const c = this.allCities.find(item => item.id == this.selectedCityId);
            return c && c.districts ? c.districts : [];
        },

        onPropertyTypeChange(newType) {
            this.propertyType = newType;
            if (newType === 'apartemen') {
                this.priceType = 'total';
                if (!this.unitType) {
                    if (this.bedrooms === '0') {
                        this.unitType = 'Studio';
                    } else if (this.bedrooms && parseInt(this.bedrooms) > 0) {
                        this.unitType = this.bedrooms + 'BR';
                    } else {
                        this.bedrooms = '2';
                        this.unitType = '2BR';
                    }
                }
                if (!this.bathrooms) {
                    this.bathrooms = '1';
                }
                if (!this.certificate || this.certificate === 'SHM') {
                    this.certificate = 'Strata';
                }
            } else if (newType === 'rumah') {
                if (!this.certificate || this.certificate === 'Strata') {
                    this.certificate = 'SHM';
                }
            } else if (newType === 'ruko' || newType === 'kantor' || newType === 'tempat_usaha') {
                if (!this.floors) {
                    this.floors = '3';
                }
                if (!this.bathrooms) {
                    this.bathrooms = '2';
                }
                if (!this.certificate || this.certificate === 'Strata') {
                    this.certificate = 'HGB';
                }
            } else if (newType === 'gudang' || newType === 'pabrik') {
                if (!this.floors) {
                    this.floors = '1';
                }
                if (!this.certificate || this.certificate === 'Strata') {
                    this.certificate = 'HGB';
                }
                if (!this.electricity) {
                    this.electricity = '16500';
                }
            } else if (['tanah', 'tanah_residensial', 'tanah_komersial'].includes(newType)) {
                if (!this.certificate || this.certificate === 'Strata') {
                    this.certificate = 'SHM';
                }
                this.floors = '';
                this.bedrooms = '';
                this.bathrooms = '';
                this.buildingArea = '';
            }
            if (this.isAutoCode) {
                this.updateAutoCode();
            }
            // Reset selected project jika tidak lagi cocok dengan filter tipe baru
            if (this.selectedProjectId) {
                const stillValid = this.filteredProjects.some(p => p.id == this.selectedProjectId);
                if (!stillValid) {
                    this.selectedProjectId = '';
                }
            }
        },

        toTitleCase(text) {
            if (!text) return '';
            return text.replace(/(?:^|\s|-)\S/g, char => char.toUpperCase());
        },

        init() {
            if (this.selectedCityId) {
                const found = this.allCities.find(c => c.id == this.selectedCityId);
                if (found) this.citySearch = found.name;
            } else if (this.customCity) {
                this.citySearch = this.toTitleCase(this.customCity);
            }
            if (this.selectedProjectId) {
                const p = this.allProjects.find(item => item.id == this.selectedProjectId);
                if (p) this.projSearch = p.name;
            } else if (this.customProjectName) {
                this.projSearch = this.toTitleCase(this.customProjectName);
            }
            if (this.price) this.formattedPrice = Number(this.price).toLocaleString('id-ID');
            if (this.originalPrice) this.formattedOriginalPrice = Number(this.originalPrice).toLocaleString('id-ID');
            if (this.monthlyRentPrice) this.formattedMonthlyRentPrice = Number(this.monthlyRentPrice).toLocaleString('id-ID');
        },

        onCityInput(e) {
            const el = e.target;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const formatted = this.toTitleCase(el.value);
            this.citySearch = formatted;
            this.$nextTick(() => {
                if (el && el.setSelectionRange) {
                    el.setSelectionRange(start, end);
                }
            });
            this.openCityList = true;
            const match = this.allCities.find(c => c.name.toLowerCase() === formatted.trim().toLowerCase());
            this.selectedCityId = match ? match.id : '';
            this.onCityChange();
        },

        onProjInput(e) {
            const el = e.target;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const formatted = this.toTitleCase(el.value);
            this.projSearch = formatted;
            this.$nextTick(() => {
                if (el && el.setSelectionRange) {
                    el.setSelectionRange(start, end);
                }
            });
            this.openProjList = true;
            const match = this.filteredProjects.find(p => p.name.toLowerCase() === formatted.trim().toLowerCase());
            if (match) {
                this.onProjectSelect(match.id);
            } else {
                this.selectedProjectId = '';
            }
        },

        onCityChange() {
            if (this.selectedProjectId) {
                const stillValid = this.filteredProjects.some(p => p.id == this.selectedProjectId);
                if (!stillValid) {
                    this.selectedProjectId = '';
                    this.projSearch = '';
                }
            }
        },

        onProjectSelect(eOrId) {
            const id = (typeof eOrId === 'object' && eOrId && eOrId.target) ? eOrId.target.value : eOrId;
            this.selectedProjectId = id;
            if (!id) return;
            const proj = this.allProjects.find(item => item.id == id);
            if (proj) {
                if (proj.city_id && !this.selectedCityId) {
                    this.selectedCityId = proj.city_id;
                    const c = this.allCities.find(item => item.id == proj.city_id);
                    if (c) this.citySearch = c.name;
                    this.onCityChange();
                }
                const addrInput = document.getElementById('prop_address');
                if (addrInput && proj.address && !addrInput.value) {
                    addrInput.value = proj.address;
                }
                if (proj.district_id) {
                    this.selectedDistrictId = proj.district_id;
                }
            }
        },

        get computedPropertyCode() {
            const src = (this.codeSource || 'A').toUpperCase();
            const typeMap = {
                apartemen: 'APT',
                rumah: 'RMH',
                tanah: 'TNH',
                tanah_residensial: 'TNH',
                tanah_komersial: 'TNH',
                ruko: 'RUKO',
                gudang: 'GDG',
                villa: 'VLA',
                kantor: 'KTR',
                tempat_usaha: 'USHA',
                pabrik: 'PBRK',
                kost: 'KOST',
                hotel: 'HTL',
                gedung: 'GDN'
            };
            const typeCode = typeMap[this.propertyType] || 'RMH';
            const seq = String(this.nextSequences[this.propertyType] || this.nextSequences['apartemen'] || 1).padStart(4, '0');
            const txCode = (this.listingType === 'disewa') ? 'S' : 'J';
            return `${src}-${typeCode}-${seq}-${txCode}`;
        },

        updateAutoCode() {
            if (this.isAutoCode) {
                this.propertyCode = this.computedPropertyCode;
            }
        },

        generateFreshCode() {
            this.isAutoCode = true;
            this.propertyCode = this.computedPropertyCode;
        },

        get generatedPrefix() {
            let parts = [];

            // 1. Label Transaksi
            let tx = this.listingType === 'disewa' ? 'Disewakan' : 'Dijual';
            parts.push(tx);

            // 2. Kategori Properti
            const typeMap = {
                rumah: 'Rumah',
                apartemen: 'Apartemen',
                tanah: 'Tanah',
                tanah_residensial: 'Tanah',
                tanah_komersial: 'Tanah',
                gudang: 'Gudang',
                ruko: 'Ruko',
                hotel: 'Hotel',
                gedung: 'Gedung',
                villa: 'Villa',
                tempat_usaha: 'Tempat Usaha',
                kantor: 'Kantor',
                pabrik: 'Pabrik',
                kost: 'Kost'
            };
            parts.push(typeMap[this.propertyType] || 'Rumah');

            // 3. Nama Gedung / Project / Cluster
            let projName = '';
            if (this.projectInputMode === 'custom' && this.customProjectName.trim()) {
                let clean = this.customProjectName.replace(/^(Apartemen|Ruko|Perumahan|Cluster|Kavling|Gudang)\s+/i, '').trim();
                projName = clean || this.customProjectName.trim();
            } else if (this.selectedProjectId) {
                const p = this.allProjects.find(item => item.id == this.selectedProjectId);
                if (p) {
                    let clean = p.name.replace(/^(Apartemen|Ruko|Perumahan|Cluster|Kavling|Gudang)\s+/i, '').trim();
                    projName = clean || p.name;
                }
            }
            if (projName) {
                parts.push(projName);
            }

            // 4. Spesifikasi Berdasarkan Tipe
            if (this.propertyType === 'apartemen') {
                let uType = (this.unitType && this.unitType.trim())
                    ? this.unitType.trim().replace(/ /g, '')
                    : (this.bedrooms === '0' ? 'Studio' : (this.bedrooms ? this.bedrooms + 'BR' : ''));
                if (uType) {
                    parts.push(uType);
                }
                if (this.unitTower && this.unitTower.trim()) {
                    let tw = this.unitTower.trim();
                    if (!/^tower/i.test(tw)) tw = 'Tower ' + tw;
                    parts.push(tw);
                }
                if (this.unitFloor && this.unitFloor.trim()) {
                    let fl = this.unitFloor.trim();
                    if (!/^(lt|lantai)/i.test(fl)) fl = 'Lantai ' + fl;
                    parts.push(fl);
                }
                if (this.furnishedStatus === 'fully-furnished') {
                    parts.push('Full Furnished');
                } else if (this.furnishedStatus === 'semi-furnished') {
                    parts.push('Semi Furnished');
                } else if (this.furnishedStatus === 'unfurnished') {
                    parts.push('Kosongan');
                }
            } else if (this.propertyType === 'rumah') {
                if (this.floors && parseInt(this.floors) > 1) {
                    parts.push(parseInt(this.floors) + ' Lantai');
                }
                if (this.bedrooms && parseInt(this.bedrooms) > 0) {
                    let kt = 'KT ' + parseInt(this.bedrooms);
                    if (this.maidBedrooms && parseInt(this.maidBedrooms) > 0) {
                        kt += '+' + parseInt(this.maidBedrooms);
                    }
                    parts.push(kt);
                }
                if (this.landArea && parseFloat(this.landArea) > 0) {
                    parts.push('LT ' + parseFloat(this.landArea) + 'm²');
                }
                if (this.furnishedStatus === 'fully-furnished') {
                    parts.push('Full Furnished');
                } else if (this.furnishedStatus === 'semi-furnished') {
                    parts.push('Semi Furnished');
                }
            } else if (['ruko', 'gedung', 'kantor', 'tempat_usaha'].includes(this.propertyType)) {
                if (this.floors && parseInt(this.floors) > 0) {
                    parts.push(parseInt(this.floors) + ' Lantai');
                }
                if (this.buildingArea && parseFloat(this.buildingArea) > 0) {
                    parts.push('LB ' + parseFloat(this.buildingArea) + 'm²');
                }
                if (this.dimension && this.dimension.trim()) {
                    parts.push(this.dimension.trim());
                }
                if (this.furnishedStatus === 'fully-furnished') {
                    parts.push('Full Furnished');
                } else if (this.furnishedStatus === 'semi-furnished') {
                    parts.push('Semi Furnished');
                }
            } else if (['tanah', 'tanah_residensial', 'tanah_komersial'].includes(this.propertyType)) {
                if (this.landArea && parseFloat(this.landArea) > 0) {
                    parts.push('LT ' + parseFloat(this.landArea) + (this.areaUnit === 'are' ? ' are' : 'm²'));
                } else if (this.dimension && this.dimension.trim()) {
                    parts.push(this.dimension.trim());
                }
            } else if (['gudang', 'pabrik'].includes(this.propertyType)) {
                if (this.floors && parseInt(this.floors) > 1) {
                    parts.push(parseInt(this.floors) + ' Lantai');
                }
                if (this.landArea && parseFloat(this.landArea) > 0) {
                    let dims = 'LT ' + parseFloat(this.landArea) + 'm²';
                    if (this.buildingArea && parseFloat(this.buildingArea) > 0) {
                        dims += ' LB ' + parseFloat(this.buildingArea) + 'm²';
                    }
                    parts.push(dims);
                } else if (this.buildingArea && parseFloat(this.buildingArea) > 0) {
                    parts.push('LB ' + parseFloat(this.buildingArea) + 'm²');
                }
                if (this.dimension && this.dimension.trim()) {
                    parts.push(this.dimension.trim());
                }
                if (this.furnishedStatus === 'fully-furnished') {
                    parts.push('Full Furnished');
                } else if (this.furnishedStatus === 'semi-furnished') {
                    parts.push('Semi Furnished');
                }
            } else if (['villa', 'hotel', 'kost'].includes(this.propertyType)) {
                if (this.bedrooms && parseInt(this.bedrooms) > 0) {
                    parts.push(parseInt(this.bedrooms) + ' Kamar');
                }
            }

            // 5. Nama Kota
            if (this.cityInputMode === 'custom' && this.customCity.trim()) {
                parts.push(this.customCity.trim());
            } else if (this.selectedCityId) {
                const c = this.allCities.find(item => item.id == this.selectedCityId);
                if (c && c.name) {
                    parts.push(c.name);
                }
            }

            return parts.filter(Boolean).join(' ');
        },

        get fullGeneratedTitle() {
            const p = this.generatedPrefix;
            const s = (this.customSuffix || '').trim().replace(/^[-–—\s]+/, '');
            if (!s) return p;
            return p + ' - ' + s;
        },

        get estimatedPricePerM2() {
            const p = parseFloat(this.price);
            const lt = parseFloat(this.landArea) || parseFloat(this.buildingArea);
            if (!p || !lt || lt <= 0) return null;
            const perM2 = p / lt;
            if (perM2 >= 1000000000) {
                return 'Rp ' + (perM2 / 1000000000).toFixed(2).replace(/\.00$/, '') + ' Miliar / m²';
            } else if (perM2 >= 1000000) {
                return 'Rp ' + (perM2 / 1000000).toFixed(1).replace(/\.0$/, '') + ' Jt / m²';
            } else {
                return 'Rp ' + Math.round(perM2).toLocaleString('id-ID') + ' / m²';
            }
        }
     }">

    <form action="{{ route('admin.properties.update', $property->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 sm:space-y-3.5" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
        @csrf
        @method('PUT')

        <!-- Unified Form Inputs Bound to Alpine State -->
        <input type="hidden" name="property_type" :value="propertyType">
        <input type="hidden" name="listing_type" :value="listingType">
        <input type="hidden" name="code_source" :value="codeSource">
        <input type="hidden" name="bedrooms" :value="bedrooms">
        <input type="hidden" name="maid_bedrooms" :value="maidBedrooms">
        <input type="hidden" name="bathrooms" :value="bathrooms">
        <input type="hidden" name="maid_bathrooms" :value="maidBathrooms">
        <input type="hidden" name="floors" :value="floors">
        <input type="hidden" name="carports" :value="carports">
        <input type="hidden" name="land_area" :value="landArea">
        <input type="hidden" name="building_area" :value="buildingArea">
        <input type="hidden" name="furnished_status" :value="furnishedStatus">
        <input type="hidden" name="certificate" :value="certificate">
        <input type="hidden" name="facing" :value="facing">
        <input type="hidden" name="road_width" :value="roadWidth">
        <input type="hidden" name="electricity" :value="electricity">
        <input type="hidden" name="dimension" :value="dimension">
        <input type="hidden" name="unit_type" :value="unitType">
        <input type="hidden" name="unit_tower" :value="unitTower">
        <input type="hidden" name="unit_floor" :value="unitFloor">
        <input type="hidden" name="unit_number" :value="unitNumber">
        <input type="hidden" name="unit_view" :value="unitView">

        <input type="hidden" name="agent_id" value="{{ old('agent_id', $property->agent_id) }}">
        <input type="hidden" name="has_photo_management" value="1">

        <!-- ======================================================== -->
        <!-- 1. KATEGORI PROPERTI, TIPE IKLAN & KODE LISTING -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-2xs space-y-3.5">
            <!-- Bagian 1: Kategori Properti (Clean 4-Col Grid) -->
            <div class="space-y-2.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-800">
                        Kategori Properti <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kategori Properti">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih kategori properti yang sesuai dengan listing Anda.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-1 min-[370px]:gap-1.5 sm:gap-2.5">
                    @php
                        $categoryTypes = [
                            ['id' => 'rumah', 'label' => 'Rumah', 'icon' => 'fa-solid fa-house-chimney'],
                            ['id' => 'apartemen', 'label' => 'Apartemen', 'icon' => 'fa-solid fa-building'],
                            ['id' => 'tanah', 'label' => 'Tanah', 'icon' => 'fa-solid fa-map'],
                            ['id' => 'ruko', 'label' => 'Ruko', 'icon' => 'fa-solid fa-store'],
                            ['id' => 'gudang', 'label' => 'Gudang', 'icon' => 'fa-solid fa-warehouse'],
                            ['id' => 'hotel', 'label' => 'Hotel', 'icon' => 'fa-solid fa-hotel'],
                            ['id' => 'gedung', 'label' => 'Gedung', 'icon' => 'fa-solid fa-city'],
                            ['id' => 'villa', 'label' => 'Villa', 'icon' => 'fa-solid fa-umbrella-beach'],
                        ];
                    @endphp
                    @foreach($categoryTypes as $cat)
                        <button type="button"
                                @click="onPropertyTypeChange('{{ $cat['id'] }}')"
                                :class="propertyType === '{{ $cat['id'] }}'
                                    ? 'bg-pink-50 border-pink-500 text-pink-700 font-bold shadow-2xs'
                                    : 'bg-slate-50/80 hover:bg-pink-50/60 border-slate-200 text-slate-700 hover:text-pink-600 hover:border-pink-300 font-medium sm:font-semibold'"
                                class="flex flex-col items-center justify-center px-0.5 py-1.5 sm:py-2.5 sm:px-2 rounded-[10px] sm:rounded-xl border text-center transition-all cursor-pointer group min-w-0">
                            <i class="{{ $cat['icon'] }} text-sm sm:text-lg mb-1 group-hover:scale-110 transition-transform"
                               :class="propertyType === '{{ $cat['id'] }}' ? 'text-pink-600' : 'text-slate-500 group-hover:text-pink-600'"></i>
                            <span class="block w-full truncate leading-tight px-0.5 {{ $cat['id'] === 'apartemen' ? 'text-[8.5px] min-[370px]:text-[9.5px] min-[410px]:text-[10.5px] sm:text-xs tracking-tighter' : 'text-[9.5px] min-[370px]:text-[10px] min-[410px]:text-[11px] sm:text-xs tracking-tight sm:tracking-normal' }}">{{ $cat['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Separator -->
            <div class="border-t border-slate-100"></div>

            <!-- Bagian 2: Tipe Iklan (2 Buttons: Dijual, Disewa) -->
            <div class="space-y-2">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-800">
                        Tipe Iklan <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Tipe Iklan">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih status ketersediaan transaksi properti (Dijual atau Disewa).
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:gap-2.5 max-w-xs">
                    <button type="button"
                            @click="listingType = 'dijual'; if (isAutoCode) updateAutoCode();"
                            :class="listingType === 'dijual' ? 'border-2 border-emerald-600 bg-emerald-50 text-emerald-800 shadow-xs font-black' : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-bold'"
                            class="py-2 px-3 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-tag text-xs" :class="listingType === 'dijual' ? 'text-emerald-600' : 'text-slate-400'"></i>
                        <span>Dijual</span>
                    </button>

                    <button type="button"
                            @click="listingType = 'disewa'; if (isAutoCode) updateAutoCode();"
                            :class="listingType === 'disewa' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 shadow-xs font-black' : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-bold'"
                            class="py-2 px-3 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-key text-xs" :class="listingType === 'disewa' ? 'text-pink-600' : 'text-slate-400'"></i>
                        <span>Disewa</span>
                    </button>
                </div>
            </div>

            <!-- Separator -->
            <div class="border-t border-slate-100"></div>

            <!-- Bagian 3: Kode Listing (Tepat di bawah Tipe Iklan) -->
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-800">
                        Kode Listing
                    </label>
                    <span class="text-[9px] bg-pink-50 text-pink-700 border border-pink-200 px-2 py-0.5 rounded font-extrabold uppercase"
                          x-text="isAutoCode ? 'Auto' : 'Tersimpan'">
                    </span>

                    <!-- Toggle Sumber: Agen vs Vendor (Dipepetkan langsung dengan Auto/Tersimpan) -->
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-lg border border-slate-200">
                        <button type="button"
                                @click="codeSource = 'A'; if (isAutoCode) updateAutoCode();"
                                :class="codeSource === 'A' ? 'bg-pink-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded text-xs transition cursor-pointer">
                            Agen
                        </button>
                        <button type="button"
                                @click="codeSource = 'V'; if (isAutoCode) updateAutoCode();"
                                :class="codeSource === 'V' ? 'bg-pink-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded text-xs transition cursor-pointer">
                            V (Vendor)
                        </button>
                    </div>

                    <!-- Tooltip Info Format Kode -->
                    <div class="relative inline-flex items-center"
                         x-data="{ showCodeTip: false }"
                         @mouseenter="showCodeTip = true"
                         @mouseleave="showCodeTip = false">
                        <button type="button"
                                @click="showCodeTip = !showCodeTip"
                                class="w-6 h-6 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer"
                                aria-label="Info Format Kode">
                            <i class="fa-solid fa-circle-info text-sm"></i>
                        </button>
                        <div x-show="showCodeTip"
                             x-cloak
                             @click.outside="showCodeTip = false"
                             class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-64 sm:w-72 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Format kode: <strong class="text-pink-300">[Agen]-[Tipe]-[Nomor Urut]-[J/S]</strong>. Membedakan agen pembuat listing secara ringkas.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2.5 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 max-w-xl">
                    <input type="text"
                           name="property_code"
                           x-model="propertyCode"
                           class="w-full px-3 py-2 bg-pink-50/50 text-pink-700 border border-pink-200 rounded-xl text-xs sm:text-sm font-mono font-black tracking-widest focus:outline-none focus:ring-2 focus:ring-pink-500 transition">

                    <button type="button"
                            @click="generateFreshCode()"
                            title="Regenerate kode otomatis baru"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 border bg-white hover:bg-pink-50 text-pink-700 border-pink-200 cursor-pointer">
                        <i class="fa-solid fa-rotate text-pink-500 text-xs"></i>
                        <span class="hidden sm:inline">Reset Auto</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. PILIH LOKASI KOTA & MASTER PROYEK / CLUSTER (DI ATAS) -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="space-y-2.5 sm:space-y-3">
                <!-- 1. Kota (Search Autocomplete / Input Langsung Otomatis Kapital) -->
                <div class="relative" @click.outside="openCityList = false">
                    <div class="flex items-center gap-1.5 mb-1">
                        <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-700">
                            1. Kota <span class="text-pink-600">*</span>
                        </label>
                        <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                            <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kota">
                                <i class="fa-solid fa-circle-info text-xs"></i>
                            </button>
                            <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 sm:w-64 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                                Ketik untuk mencari master kota atau tambah kota baru.
                                <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                                <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Input untuk form submission: jika ada id master pakai city_id, jika baru pakai custom_city -->
                    <input type="hidden" name="city_id" :value="selectedCityId">
                    <input type="hidden" name="custom_city" :value="!selectedCityId ? citySearch : ''">

                    <div class="relative">
                        <input type="text"
                               x-model="citySearch"
                               @input="onCityInput($event)"
                               @focus="openCityList = true"
                               placeholder="Cari atau ketik kota (Cth: Surabaya, Jakarta, Malang)..."
                               class="w-full pl-9 pr-9 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <button type="button"
                                x-show="citySearch"
                                @click="citySearch = ''; selectedCityId = ''; onCityChange()"
                                class="absolute right-2.5 top-1.5 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Dropdown List Filtered Kota -->
                    <div x-show="openCityList && citySearch"
                         x-cloak
                         class="absolute left-0 right-0 top-full mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 py-1 text-xs divide-y divide-slate-50">
                        <template x-for="c in allCities.filter(item => item.name.toLowerCase().includes(citySearch.toLowerCase()))" :key="c.id">
                            <button type="button"
                                    @click="selectedCityId = c.id; citySearch = c.name; openCityList = false; onCityChange()"
                                    class="w-full text-left px-3.5 py-1.5 hover:bg-pink-50 hover:text-pink-700 flex items-center justify-between transition cursor-pointer"
                                    :class="selectedCityId == c.id ? 'bg-pink-50/70 font-bold text-pink-700' : 'text-slate-700'">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-pink-500 text-xs"></i>
                                    <span x-text="c.name"></span>
                                </div>
                                <span class="text-xs text-slate-400" x-text="c.province ? c.province.name : ''"></span>
                            </button>
                        </template>
                        <div x-show="allCities.filter(item => item.name.toLowerCase().includes(citySearch.toLowerCase())).length === 0" class="p-3 text-slate-600 text-xs flex items-center justify-between">
                            <span>Kota "<strong><span x-text="citySearch"></span></strong>" belum ada di master (akan otomatis tersimpan).</span>
                            <span class="text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full font-bold">Kota Baru</span>
                        </div>
                    </div>

                    <div class="mt-1 flex items-center gap-1.5 text-xs" x-show="citySearch">
                        <template x-if="selectedCityId">
                            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-xs"></i> Master Kota Terpilih
                            </span>
                        </template>
                        <template x-if="!selectedCityId && citySearch">
                            <span class="text-pink-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-plus text-xs"></i> Kota Baru (Tersimpan otomatis ke database saat disimpan)
                            </span>
                        </template>
                    </div>
                </div>

                <!-- 2. Master Apartemen / Cluster / Proyek (Di Bawah Kota, Otomatis Kapital) -->
                <div class="relative" @click.outside="openProjList = false">
                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                        <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-700">
                            <span>2. <span x-text="projectDropdownLabel"></span></span>
                            <span class="text-xs text-pink-600 font-semibold lowercase"
                                  x-show="selectedCityId"
                                  x-text="'(filter: ' + propertyType.toUpperCase() + ' di kota terpilih)'"></span>
                        </label>
                        <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                            <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Nama Proyek">
                                <i class="fa-solid fa-circle-info text-xs"></i>
                            </button>
                            <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-64 sm:w-72 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                                Ketik nama untuk mencari atau tambah baru (otomatis kapital di tiap kata).
                                <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                                <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Input untuk form submission: jika ada id master pakai project_id, jika baru pakai custom_project_name -->
                    <input type="hidden" name="project_id" :value="selectedProjectId">
                    <input type="hidden" name="custom_project_name" :value="!selectedProjectId ? projSearch : ''">

                    <div class="relative">
                        <input type="text"
                               x-model="projSearch"
                               @input="onProjInput($event)"
                               @focus="openProjList = true"
                               :placeholder="'Cari atau ketik nama ' + (propertyType === 'apartemen' ? 'apartemen' : 'cluster/perumahan') + ' (Cth: Cluster Ayara, Benson)...'"
                               class="w-full pl-9 pr-9 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none transition">
                        <i class="fa-solid fa-building-circle-check absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <button type="button"
                                x-show="projSearch"
                                @click="projSearch = ''; selectedProjectId = ''"
                                class="absolute right-2.5 top-1.5 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Dropdown List Filtered Projects -->
                    <div x-show="openProjList && projSearch"
                         x-cloak
                         class="absolute left-0 right-0 top-full mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 py-1 text-xs divide-y divide-slate-50">
                        <!-- Opsi Mandiri -->
                        <button type="button"
                                @click="selectedProjectId = ''; projSearch = ''; openProjList = false"
                                class="w-full text-left px-3.5 py-1.5 hover:bg-slate-50 text-slate-500 italic transition cursor-pointer">
                            -- Properti Mandiri / Bukan Bagian Kompleks --
                        </button>

                        <template x-for="p in filteredProjects.filter(item => item.name.toLowerCase().includes(projSearch.toLowerCase()))" :key="p.id">
                            <button type="button"
                                    @click="selectedProjectId = p.id; projSearch = p.name; openProjList = false; onProjectSelect(p.id)"
                                    class="w-full text-left px-3.5 py-1.5 hover:bg-pink-50 hover:text-pink-700 flex items-center justify-between transition cursor-pointer"
                                    :class="selectedProjectId == p.id ? 'bg-pink-50/70 font-bold text-pink-700' : 'text-slate-700'">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-building-circle-check text-pink-500 text-xs"></i>
                                    <span x-text="p.name"></span>
                                </div>
                                <span class="text-xs text-slate-400" x-text="p.city ? p.city.name : ''"></span>
                            </button>
                        </template>

                        <div x-show="filteredProjects.filter(item => item.name.toLowerCase().includes(projSearch.toLowerCase())).length === 0" class="p-3 text-slate-600 text-xs flex items-center justify-between">
                            <span>Nama "<strong><span x-text="projSearch"></span></strong>" belum ada di master (akan otomatis tersimpan).</span>
                            <span class="text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full font-bold">Baru</span>
                        </div>
                    </div>

                    <div class="mt-1 flex items-center gap-1.5 text-xs" x-show="projSearch">
                        <template x-if="selectedProjectId">
                            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-xs"></i> Master Terdata
                            </span>
                        </template>
                        <template x-if="!selectedProjectId && projSearch">
                            <span class="text-pink-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-plus text-xs"></i> Data Baru (Tersimpan otomatis ke master &amp; judul listing)
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Alamat Lengkap / Nama Jalan (Kecamatan tidak perlu & Alamat tidak required) -->
            <div class="pt-0.5">
                <label class="block text-xs font-bold text-slate-700 mb-0.5">Nama Jalan Utama / Alamat (Opsional)</label>
                <input type="text"
                       id="prop_address"
                       name="address"
                       placeholder="Cth: Jl. San Antonio Blok N1 No. 12 (boleh dikosongkan)"
                       value="{{ old('address', $property->address) }}"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 4. STANDARISASI JUDUL LISTING (OTOMATIS MENGAMBIL DATA ATAS) -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-3.5 sm:p-4 shadow-2xs space-y-2.5">
            <!-- Live Result Preview -->
            <div class="bg-pink-50/40 rounded-xl border border-pink-200 p-3 sm:p-3.5 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold text-pink-700">
                    <span class="uppercase tracking-wider">Preview Hasil Judul Tayang:</span>
                </div>
                <p class="text-sm sm:text-base font-black text-slate-900 leading-snug break-words"
                   x-text="fullGeneratedTitle || 'Silakan pilih kategori dan data properti...'"></p>
            </div>

            <!-- Tambahan Judul -->
            <div class="pt-0.5">
                <div class="flex items-center gap-1.5 mb-0.5">
                    <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-pen text-pink-600 text-[10px]"></i>
                        <span>Tambahan Judul</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Tambahan Judul">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 sm:w-64 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Tambahkan keterangan unik listing Anda (opsional).
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>
                <input type="text"
                       name="title_suffix"
                       x-model="customSuffix"
                       placeholder="Cth: Pool View, Hook Hadap Utara, Full Marmer, Dekat Kampus."
                       class="w-full px-3 py-2 bg-white text-slate-900 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none placeholder:text-slate-400 placeholder:font-normal transition">
            </div>

            <!-- Hidden Input for Form Submission -->
            <input type="hidden" name="title" id="prop_title" :value="fullGeneratedTitle">
        </div>

        <!-- ======================================================== -->
        <!-- 5. SPESIFIKASI DINAMIS BERDASARKAN TIPE PROPERTI -->
        <!-- ======================================================== -->

        <!-- CASE A: APARTEMEN (PERSIS PINHOME) -->
        <div x-show="propertyType === 'apartemen'" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center gap-1.5 border-b border-slate-100 pb-1.5">
                <h2 class="text-xs sm:text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-building text-pink-600"></i>
                    <span>Spesifikasi</span>
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Spesifikasi Apartemen">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Format standar industri listing properti.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. Kamar Tidur * ([Studio] + (1) s/d (10)) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Tidur <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Tidur">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih Studio atau jumlah kamar tidur (1 - 10).
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Pill: Studio -->
                    <button type="button"
                            @click="bedrooms = '0'; unitType = 'Studio'"
                            :class="(bedrooms == '0' || unitType.toLowerCase() === 'studio')
                                    ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-bed text-xs"></i>
                        <span>Studio</span>
                    </button>

                    <!-- Circle Buttons: 1 to 10 -->
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="bedrooms = '{{ $num }}'; unitType = '{{ $num }}BR'"
                                :class="(bedrooms == '{{ $num }}' && unitType.toLowerCase() !== 'studio')
                                        ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105'
                                        : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 2. Kamar Mandi * (Circle Buttons 1 to 10) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Mandi <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Mandi">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih jumlah kamar mandi unit (1 - 10).
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="bathrooms = '{{ $num }}'"
                                :class="bathrooms == '{{ $num }}'
                                        ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105'
                                        : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Luas Bangunan * (m² suffix) -->
            <div class="max-w-md">
                <div class="flex items-center gap-1.5 mb-1">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Luas Bangunan <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Luas Bangunan">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Luas semi gross atau netto unit apartemen.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <input type="number"
                           step="any"
                           x-model="buildingArea"
                           placeholder="Masukkan luas bangunan"
                           class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none">
                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none">m²</span>
                </div>
            </div>

            <!-- 4. Sertifikat * (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Sertifikat <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach(['SHM' => 'SHM', 'HGB' => 'HGB', 'Girik' => 'Girik', 'Strata' => 'Strata', 'PPJB' => 'PPJB', 'Lainnya' => 'Lainnya'] as $key => $lbl)
                        <button type="button"
                                @click="certificate = '{{ $key }}'"
                                :class="certificate === '{{ $key }}'
                                        ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                        : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 5. Perabot * (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Perabot <span class="text-pink-600">*</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            @click="furnishedStatus = 'fully-furnished'"
                            :class="furnishedStatus === 'fully-furnished'
                                    ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Furnished
                    </button>

                    <button type="button"
                            @click="furnishedStatus = 'semi-furnished'"
                            :class="furnishedStatus === 'semi-furnished'
                                    ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Semi Furnished
                    </button>

                    <button type="button"
                            @click="furnishedStatus = 'unfurnished'"
                            :class="furnishedStatus === 'unfurnished'
                                    ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Non Furnished
                    </button>
                </div>
            </div>

            <!-- 6. Detail Posisi & Identitas Unit Apartemen -->
            <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-pink-200/80 space-y-2.5">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-door-closed text-pink-600"></i>
                    <span>Detail</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Tower</label>
                        <input type="text"
                               x-model="unitTower"
                               placeholder="Cth: A, B"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Lantai</label>
                        <input type="text"
                               x-model="unitFloor"
                               placeholder="Cth: 12"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Nomor Unit</label>
                        <input type="text"
                               x-model="unitNumber"
                               placeholder="Cth: 08"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Arah / View Unit</label>
                        <input type="text"
                               x-model="unitView"
                               placeholder="Cth: City View, Pool View"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-0.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Daya Listrik (VA)</label>
                        <input type="number"
                               x-model="electricity"
                               placeholder="Cth: 1300, 2200"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Hadap</label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach(['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'] as $dir)
                                <button type="button"
                                        @click="facing = (facing === '{{ $dir }}' ? '' : '{{ $dir }}')"
                                        :class="facing === '{{ $dir }}'
                                                ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                                : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                        class="px-2.5 py-1 rounded-xl text-xs transition cursor-pointer">
                                    {{ $dir }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CASE B: RUMAH -->
        <div x-show="propertyType === 'rumah'" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-1.5 flex items-center gap-2">
                <i class="fa-solid fa-house-chimney text-pink-600"></i>
                <span>Spesifikasi</span>
            </h2>

            <!-- Kamar Tidur (Circle Buttons 1-8+) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Tidur <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Tidur Rumah">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih jumlah kamar tidur utama.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8] as $num)
                        <button type="button"
                                @click="bedrooms = '{{ $num }}'"
                                :class="bedrooms == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}{{ $num == 8 ? '+' : '' }}
                        </button>
                    @endforeach

                    <!-- Plus KT ART -->
                    <div class="inline-flex items-center gap-1.5 ml-1 pl-2.5 border-l border-slate-200">
                        <span class="text-xs text-slate-500 font-bold">+ ART:</span>
                        <input type="number"
                               x-model="maidBedrooms"
                               placeholder="0"
                               min="0"
                               max="10"
                               class="w-11 px-2 py-1 text-center bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Kamar Mandi (Circle Buttons 1-7+) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Mandi <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Mandi Rumah">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih jumlah kamar mandi utama.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8] as $num)
                        <button type="button"
                                @click="bathrooms = '{{ $num }}'"
                                :class="bathrooms == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}{{ $num == 8 ? '+' : '' }}
                        </button>
                    @endforeach

                    <!-- Plus KM ART -->
                    <div class="inline-flex items-center gap-1.5 ml-1 pl-2.5 border-l border-slate-200">
                        <span class="text-xs text-slate-500 font-bold">+ ART:</span>
                        <input type="number"
                               x-model="maidBathrooms"
                               placeholder="0"
                               min="0"
                               max="10"
                               class="w-11 px-2 py-1 text-center bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Jumlah Lantai Properti (Circle Buttons 1-5+) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Jumlah Lantai Properti <span class="text-pink-600">*</span>
                </label>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5] as $num)
                        <button type="button"
                                @click="floors = '{{ $num }}'"
                                :class="floors == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}{{ $num == 5 ? '+' : '' }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Carport / Garasi (Circle Buttons 0-4+) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Kapasitas Carport / Garasi Mobil
                </label>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([0, 1, 2, 3, 4] as $num)
                        <button type="button"
                                @click="carports = '{{ $num }}'"
                                :class="carports == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3 h-8 sm:h-9 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}{{ $num == 4 ? '+' : '' }} Mobil
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Luas Tanah & Luas Bangunan (Input with m² Suffix) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-1">
                        Luas Tanah <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="number"
                               step="any"
                               x-model="landArea"
                               placeholder="Ketik luas tanah"
                               value="{{ old('land_area', $property->land_area) }}"
                               class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none">m²</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 mb-1">
                        Luas Bangunan <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="number"
                               step="any"
                               x-model="buildingArea"
                               placeholder="Ketik luas bangunan"
                               value="{{ old('building_area', $property->building_area) }}"
                               class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none">m²</span>
                    </div>
                </div>
            </div>

            <!-- Perabot / Furnished (Pills) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Kondisi Perabot <span class="text-pink-600">*</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            @click="furnishedStatus = 'fully-furnished'"
                            :class="furnishedStatus === 'fully-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Full Furnished
                    </button>

                    <button type="button"
                            @click="furnishedStatus = 'semi-furnished'"
                            :class="furnishedStatus === 'semi-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Semi Furnished
                    </button>

                    <button type="button"
                            @click="furnishedStatus = 'unfurnished'"
                            :class="furnishedStatus === 'unfurnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Non Furnished (Kosongan)
                    </button>
                </div>
            </div>

            <!-- Sertifikat (Pills) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Sertifikat Kepemilikan <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach(['SHM' => 'SHM (Hak Milik)', 'HGB' => 'HGB (Guna Bangunan)', 'PPJB' => 'PPJB', 'Girik' => 'Girik / Petok D', 'Strata' => 'Strata Title', 'Lainnya' => 'Lainnya'] as $key => $lbl)
                        <button type="button"
                                @click="certificate = '{{ $key }}'"
                                :class="certificate === '{{ $key }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Hadap & Akses Jalan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Hadap</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach(['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'] as $dir)
                            <button type="button"
                                    @click="facing = (facing === '{{ $dir }}' ? '' : '{{ $dir }}')"
                                    :class="facing === '{{ $dir }}'
                                            ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                            : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                                {{ $dir }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Akses Jalan / Lebar Row</label>
                    <input type="text"
                           x-model="roadWidth"
                           placeholder="Cth: Row 3 Mobil, 8 Meter"
                           value="{{ old('road_width', $property->road_width) }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Daya Listrik (VA / Watt)</label>
                    <input type="number"
                           x-model="electricity"
                           placeholder="Cth: 2200, 3500, 4400"
                           value="{{ old('electricity', $property->electricity ?? '2200') }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>

        <!-- CASE C: RUKO / GEDUNG / KANTOR / TEMPAT USAHA (PERSIS PINHOME) -->
        <div x-show="['ruko', 'gedung', 'kantor', 'tempat_usaha'].includes(propertyType)" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center gap-1.5 border-b border-slate-100 pb-1.5">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-store text-pink-600" x-show="propertyType === 'ruko'"></i>
                    <i class="fa-solid fa-city text-pink-600" x-show="propertyType === 'gedung'"></i>
                    <i class="fa-solid fa-briefcase text-pink-600" x-show="!['ruko', 'gedung'].includes(propertyType)"></i>
                    <span>Spesifikasi</span>
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Spesifikasi Ruko">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Format standar industri listing properti.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. Kamar Mandi * (Circle Buttons 1-10 + >10 ala Pinhome) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Mandi <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Mandi Ruko">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih jumlah kamar mandi.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="bathrooms = '{{ $num }}'"
                                :class="bathrooms == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                    <button type="button"
                            @click="bathrooms = '11'"
                            :class="bathrooms == '11' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-2.5 h-8 sm:h-9 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                        &gt;10
                    </button>
                </div>
            </div>

            <!-- 2. Satuan Pengukuran & Luas Bangunan & Luas Tanah -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Satuan Pengukuran &amp; Luas Bangunan <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                    </label>
                    <!-- Satuan Pengukuran (m2 / are ala Pinhome) -->
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-xl border border-slate-200">
                        <button type="button"
                                @click="areaUnit = 'm2'"
                                :class="areaUnit === 'm2' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            m²
                        </button>
                        <button type="button"
                                @click="areaUnit = 'are'"
                                :class="areaUnit === 'are' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            are
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Luas Bangunan <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   x-model="buildingArea"
                                   placeholder="Masukkan luas bangunan"
                                   value="{{ old('building_area', $property->building_area) }}"
                                   class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="areaUnit === 'are' ? 'are' : 'm²'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Luas Tanah (Opsional)
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   x-model="landArea"
                                   placeholder="Masukkan luas tanah"
                                   value="{{ old('land_area', $property->land_area) }}"
                                   class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="areaUnit === 'are' ? 'are' : 'm²'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Dimensi / Ukuran Ruko
                        </label>
                        <input type="text"
                               x-model="dimension"
                               placeholder="Cth: 4.5 x 15 m"
                               value="{{ old('dimension', $property->dimension) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 3. Jumlah Lantai (Circle Buttons 1-10 ala Pinhome) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Jumlah Lantai <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Jumlah Lantai Ruko">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Tingkat lantai bangunan ruko.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="floors = '{{ $num }}'"
                                :class="floors == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 4. Sertifikat (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Sertifikat <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach(['SHM' => 'SHM', 'HGB' => 'HGB', 'Girik' => 'Girik', 'Strata' => 'Strata', 'Lainnya' => 'Lainnya'] as $key => $lbl)
                        <button type="button"
                                @click="certificate = '{{ $key }}'"
                                :class="certificate === '{{ $key }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 5. Kondisi Perabot (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Perabot <span class="text-pink-600">*</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            @click="furnishedStatus = 'fully-furnished'"
                            :class="furnishedStatus === 'fully-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Furnished
                    </button>
                    <button type="button"
                            @click="furnishedStatus = 'semi-furnished'"
                            :class="furnishedStatus === 'semi-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Semi Furnished
                    </button>
                    <button type="button"
                            @click="furnishedStatus = 'unfurnished'"
                            :class="furnishedStatus === 'unfurnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Non Furnished
                    </button>
                </div>
            </div>

            <!-- 6. Detail Tambahan Ruko (Hadap, Akses Jalan, Listrik) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Hadap</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach(['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'] as $dir)
                            <button type="button"
                                    @click="facing = (facing === '{{ $dir }}' ? '' : '{{ $dir }}')"
                                    :class="facing === '{{ $dir }}'
                                            ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                            : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                                {{ $dir }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Akses / Row Jalan</label>
                    <input type="text"
                           x-model="roadWidth"
                           placeholder="Cth: Row 4 Mobil, Nol Jalan Raya"
                           value="{{ old('road_width', $property->road_width) }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Daya Listrik (VA / Watt)</label>
                    <input type="number"
                           x-model="electricity"
                           placeholder="Cth: 2200, 4400, 5500"
                           value="{{ old('electricity', $property->electricity ?? '3500') }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>

        <!-- CASE D: TANAH (PERSIS PINHOME) -->
        <div x-show="['tanah', 'tanah_residensial', 'tanah_komersial'].includes(propertyType)" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center gap-1.5 border-b border-slate-100 pb-1.5">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-vector-square text-pink-600"></i>
                    <span>Spesifikasi</span>
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Spesifikasi Tanah">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Format standar industri listing properti.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. Satuan Pengukuran & Luas Tanah * -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Satuan Pengukuran &amp; Luas Tanah <span class="text-pink-600">*</span>
                    </label>
                    <!-- Satuan Pengukuran (m2 / are ala Pinhome) -->
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-xl border border-slate-200">
                        <button type="button"
                                @click="areaUnit = 'm2'"
                                :class="areaUnit === 'm2' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            m²
                        </button>
                        <button type="button"
                                @click="areaUnit = 'are'"
                                :class="areaUnit === 'are' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            are
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Luas Tanah <span class="text-pink-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   x-model="landArea"
                                   placeholder="Masukkan luas tanah"
                                   value="{{ old('land_area', $property->land_area) }}"
                                   class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="areaUnit === 'are' ? 'are' : 'm²'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Dimensi / Ukuran Kavling (Opsional)
                        </label>
                        <input type="text"
                               x-model="dimension"
                               placeholder="Cth: 15 x 30 m"
                               value="{{ old('dimension', $property->dimension) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 2. Sertifikat * (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Sertifikat <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach(['SHM' => 'SHM', 'HGB' => 'HGB', 'Girik' => 'Girik', 'Strata' => 'Strata', 'Lainnya' => 'Lainnya'] as $key => $lbl)
                        <button type="button"
                                @click="certificate = '{{ $key }}'"
                                :class="certificate === '{{ $key }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Detail Tambahan Tanah (Hadap, Akses Jalan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Hadap</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach(['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'] as $dir)
                            <button type="button"
                                    @click="facing = (facing === '{{ $dir }}' ? '' : '{{ $dir }}')"
                                    :class="facing === '{{ $dir }}'
                                            ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                            : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                                {{ $dir }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Akses / Row Jalan</label>
                    <input type="text"
                           x-model="roadWidth"
                           placeholder="Cth: Row 3 Mobil, Nol Jalan Raya"
                           value="{{ old('road_width', $property->road_width) }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>

        <!-- CASE E: GUDANG / PABRIK (PERSIS PINHOME) -->
        <div x-show="['gudang', 'pabrik'].includes(propertyType)" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center gap-1.5 border-b border-slate-100 pb-1.5">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-pink-600"></i>
                    <span>Spesifikasi</span>
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Spesifikasi Gudang">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Format standar industri properti komersial.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. Kamar Mandi (Circle Buttons 1-10 + >10 ala Pinhome Gudang) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Kamar Mandi
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Kamar Mandi Gudang">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Opsional untuk gudang / pabrik.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button"
                            @click="bathrooms = ''"
                            :class="!bathrooms || bathrooms == '0' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3 h-8 sm:h-9 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                        0 / -
                    </button>
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="bathrooms = '{{ $num }}'"
                                :class="bathrooms == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                    <button type="button"
                            @click="bathrooms = '11'"
                            :class="bathrooms == '11' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-2.5 h-8 sm:h-9 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                        &gt;10
                    </button>
                </div>
            </div>

            <!-- 2. Satuan Pengukuran & Luas Tanah * & Luas Bangunan * -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Satuan Pengukuran &amp; Luas Lahan <span class="text-pink-600">*</span>
                    </label>
                    <!-- Satuan Pengukuran (m2 / are ala Pinhome) -->
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-xl border border-slate-200">
                        <button type="button"
                                @click="areaUnit = 'm2'"
                                :class="areaUnit === 'm2' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            m²
                        </button>
                        <button type="button"
                                @click="areaUnit = 'are'"
                                :class="areaUnit === 'are' ? 'bg-pink-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-pink-600 font-semibold'"
                                class="px-2.5 py-0.5 rounded-lg text-xs transition cursor-pointer">
                            are
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Luas Tanah <span class="text-pink-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   x-model="landArea"
                                   placeholder="Masukkan luas tanah"
                                   value="{{ old('land_area', $property->land_area) }}"
                                   class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="areaUnit === 'are' ? 'are' : 'm²'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Luas Bangunan <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   x-model="buildingArea"
                                   placeholder="Masukkan luas bangunan"
                                   value="{{ old('building_area', $property->building_area) }}"
                                   class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="areaUnit === 'are' ? 'are' : 'm²'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">
                            Dimensi / Ukuran Kavling Gudang
                        </label>
                        <input type="text"
                               x-model="dimension"
                               placeholder="Cth: 20 x 50 m"
                               value="{{ old('dimension', $property->dimension) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 3. Jumlah Lantai (Circle Buttons 1-10 ala Pinhome) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Jumlah Lantai <span class="text-pink-600">*</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Jumlah Lantai Gudang">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-56 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Tingkat lantai bangunan gudang / kantor.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $num)
                        <button type="button"
                                @click="floors = '{{ $num }}'"
                                :class="floors == '{{ $num }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs scale-105' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold transition flex items-center justify-center cursor-pointer">
                            {{ $num }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 4. Sertifikat (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Sertifikat <span class="text-slate-400 font-normal text-xs lowercase">(opsional)</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach(['HGB' => 'HGB', 'SHM' => 'SHM', 'Girik' => 'Girik', 'Strata' => 'Strata', 'Lainnya' => 'Lainnya'] as $key => $lbl)
                        <button type="button"
                                @click="certificate = '{{ $key }}'"
                                :class="certificate === '{{ $key }}' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 5. Kondisi Perabot (Pills ala Pinhome) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Perabot <span class="text-pink-600">*</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            @click="furnishedStatus = 'fully-furnished'"
                            :class="furnishedStatus === 'fully-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Furnished
                    </button>
                    <button type="button"
                            @click="furnishedStatus = 'semi-furnished'"
                            :class="furnishedStatus === 'semi-furnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Semi Furnished
                    </button>
                    <button type="button"
                            @click="furnishedStatus = 'unfurnished'"
                            :class="furnishedStatus === 'unfurnished' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Non Furnished
                    </button>
                </div>
            </div>

            <!-- 6. Spesifikasi Gudang / Logistik (Akses Kontainer, Daya Listrik, Arah Hadap) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Akses Kontainer / Row Jalan</label>
                    <input type="text"
                           x-model="roadWidth"
                           placeholder="Cth: Kontainer 40ft, Row 4 Mobil"
                           value="{{ old('road_width', $property->road_width) }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Daya Listrik (VA / Watt)</label>
                    <input type="number"
                           x-model="electricity"
                           placeholder="Cth: 16500, 33000, 66000"
                           value="{{ old('electricity', $property->electricity ?? '16500') }}"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Hadap</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach(['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'] as $dir)
                            <button type="button"
                                    @click="facing = (facing === '{{ $dir }}' ? '' : '{{ $dir }}')"
                                    :class="facing === '{{ $dir }}'
                                            ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs'
                                            : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                                {{ $dir }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- CASE F: VILLA / HOTEL / KOST -->
        <div x-show="['villa', 'hotel', 'kost'].includes(propertyType)" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 pb-1.5 border-b border-slate-100">
                <i class="fa-solid fa-hotel text-pink-600" x-show="propertyType === 'hotel'"></i>
                <i class="fa-solid fa-umbrella-beach text-pink-600" x-show="propertyType === 'villa'"></i>
                <i class="fa-solid fa-tree-city text-pink-600" x-show="!['hotel', 'villa'].includes(propertyType)"></i>
                <span>Spesifikasi</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Kamar Tidur</label>
                    <input type="number" x-model="bedrooms" placeholder="Cth: 4" value="{{ old('bedrooms', $property->bedrooms ?? '4') }}" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Kamar Mandi</label>
                    <input type="number" x-model="bathrooms" placeholder="Cth: 4" value="{{ old('bathrooms', $property->bathrooms ?? '4') }}" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Luas Tanah (m²)</label>
                    <input type="number" step="any" x-model="landArea" placeholder="Cth: 500" value="{{ old('land_area', $property->land_area) }}" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-0.5">Luas Bangunan (m²)</label>
                    <input type="number" step="any" x-model="buildingArea" placeholder="Cth: 350" value="{{ old('building_area', $property->building_area) }}" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 6. HARGA PROPERTI & ESTIMASI NILAI JUAL (ALA PINHOME) -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-3.5 sm:p-4 shadow-2xs space-y-2.5 sm:space-y-3">
            <h2 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-1.5 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-pink-600"></i>
                <span x-text="listingType === 'dijual' ? 'Harga Jual' : 'Harga Sewa'"></span>
            </h2>

            <!-- Tipe Harga (Total vs /m² ala Pinhome) - Disembunyikan untuk apartemen karena apartemen hanya butuh harga total -->
            <div class="space-y-1 pb-0.5" x-show="propertyType !== 'apartemen'">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Tipe Harga
                </label>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="priceType = 'total'"
                            :class="priceType === 'total' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        Total
                    </button>
                    <button type="button"
                            @click="priceType = 'per_m2'"
                            :class="priceType === 'per_m2' ? 'border-2 border-pink-600 bg-pink-50 text-pink-700 font-black shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-pink-300 font-bold'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        /m²
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 items-start">
                <div>
                    <label class="block text-xs sm:text-sm font-black uppercase tracking-wider text-slate-800 min-h-[28px] flex items-end pb-1">
                        <span x-show="propertyType === 'apartemen'">Harga Total <span x-show="listingType === 'dijual'">Jual</span><span x-show="listingType === 'disewa'">Sewa</span> *</span>
                        <span x-show="propertyType !== 'apartemen' && listingType === 'dijual'">Harga Jual Utama *</span>
                        <span x-show="propertyType !== 'apartemen' && listingType === 'disewa'">Harga Sewa Utama *</span>
                    </label>

                    <!-- Input Mode Total -->
                    <template x-if="priceType === 'total'">
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-base font-black text-slate-400 pointer-events-none select-none">Rp</span>
                            <input type="text"
                                   inputmode="numeric"
                                   x-model="formattedPrice"
                                   @input="onPriceInput($event)"
                                   required
                                   placeholder="0"
                                   class="w-full pl-14 pr-3.5 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl text-base sm:text-lg font-black text-slate-900 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none transition">
                            <input type="hidden" name="price" :value="price">
                        </div>
                    </template>

                    <!-- Input Mode /m2 -->
                    <template x-if="priceType === 'per_m2'">
                        <div class="space-y-1">
                            <div class="relative flex items-center">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-base font-black text-slate-400 pointer-events-none select-none">Rp</span>
                                <input type="text"
                                       inputmode="numeric"
                                       x-model="formattedPricePerM2"
                                       @input="onPricePerM2Input($event)"
                                       placeholder="0"
                                       class="w-full pl-14 pr-12 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl text-base sm:text-lg font-black text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm font-bold text-slate-400 pointer-events-none select-none">/m²</span>
                            </div>
                            <input type="hidden" name="price" :value="price">
                            <div class="text-xs text-pink-600 font-extrabold" x-show="price">
                                Total Otomatis: Rp <span x-text="Number(price).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 min-h-[28px] flex items-end pb-1">
                        Harga Sebelum Diskon (Coret)
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-base font-black text-slate-400 pointer-events-none select-none">Rp</span>
                        <input type="text"
                               inputmode="numeric"
                               x-model="formattedOriginalPrice"
                               @input="onOriginalPriceInput($event)"
                               placeholder="Cth: 3.500.000.000"
                               class="w-full pl-14 pr-3.5 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl text-base sm:text-lg font-bold text-pink-700 focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                        <input type="hidden" name="original_price" :value="originalPrice">
                    </div>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 min-h-[28px] flex items-end pb-1">
                        Keterangan Tambahan Harga
                    </label>
                    <input type="text"
                           name="price_prefix"
                           placeholder="Cth: Nego, Nego Tipis, Nett"
                           value="{{ old('price_prefix', $property->price_prefix) }}"
                           class="w-full px-3 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Estimasi Nilai Jual Propertimu (Card Dinamis ala Pinhome) -->
            <div class="p-3 bg-pink-50/40 rounded-xl border border-pink-200 space-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-pink-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-pink-600"></i> Estimasi Nilai Jual Propertimu
                    </span>
                    <template x-if="estimatedPricePerM2">
                        <span class="text-xs font-extrabold bg-white text-pink-700 border border-pink-200 px-2 py-0.5 rounded-full shadow-2xs">
                            Kalkulasi Otomatis
                        </span>
                    </template>
                </div>
                <template x-if="estimatedPricePerM2">
                    <p class="text-xs sm:text-sm font-black text-pink-600 mt-0.5" x-text="'Perkiraan Harga: ' + estimatedPricePerM2"></p>
                </template>
                <template x-if="!estimatedPricePerM2">
                    <p class="text-xs text-slate-400 flex items-center gap-1.5 pt-0.5">
                        <i class="fa-solid fa-circle-info text-xs text-pink-500"></i> Lengkapi harga dan luas tanah/bangunan di atas untuk melihat nilai estimasi properti.
                    </p>
                </template>
            </div>

            <!-- Periode Sewa (Muncul jika Disewa) -->
            <div x-show="listingType === 'disewa'" class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5">Periode Sewa Utama</label>
                    <select name="rent_period" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <option value="tahun" {{ old('rent_period', $property->rent_period ?? 'tahun') === 'tahun' ? 'selected' : '' }}>per Tahun</option>
                        <option value="bulan" {{ old('rent_period', $property->rent_period) === 'bulan' ? 'selected' : '' }}>per Bulan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5">Pilihan Sewa Bulanan (Opsional, Rp/bln)</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm sm:text-base font-black text-slate-400 pointer-events-none select-none">Rp</span>
                        <input type="text"
                               inputmode="numeric"
                               x-model="formattedMonthlyRentPrice"
                               @input="onMonthlyRentPriceInput($event)"
                               placeholder="Cth: 5.000.000"
                               class="w-full pl-14 pr-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                        <input type="hidden" name="monthly_rent_price" :value="monthlyRentPrice">
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 7. FASILITAS TAMBAHAN & DESKRIPSI (SATU KOTAK DI BAWAH HARGA JUAL) -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center gap-1.5 pb-1.5 border-b border-slate-100">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-pink-600"></i> Fasilitas &amp; Deskripsi Properti
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Fasilitas">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Informasi fasilitas dan penjelasan lengkap listing.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. Fasilitas Tambahan (di atas deskripsi) -->
            <div class="space-y-1">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Fasilitas Tambahan <span class="text-slate-400 font-normal text-xs">(Pisahkan dengan koma)</span>
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Input Fasilitas">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-64 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Ketik fasilitas properti yang tersedia, pisahkan tiap fasilitas dengan tanda koma (,).
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>
                <textarea name="facilities_text"
                          rows="2"
                          placeholder="Row Jalan 3 Mobil, Keamanan 24 Jam, One Gate System, Kolam Renang Cluster, Taman Bermain"
                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none transition">{{ old('facilities_text', is_array($property->facilities) ? implode(', ', $property->facilities) : '') }}</textarea>
            </div>

            <!-- 2. Deskripsi Lengkap (di bawah fasilitas) -->
            <div class="space-y-1 pt-1.5 border-t border-slate-100">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                    Deskripsi Lengkap (Opsional)
                </label>
                <textarea name="description"
                          rows="4"
                          placeholder="Jelaskan keunggulan properti, renovasi, posisi unit, view, serta fasilitas pendukung terdekat (boleh dikosongkan)..."
                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none transition">{{ old('description', $property->description) }}</textarea>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 8. FOTO & VIDEO PROPERTI (SATU KOTAK) -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-3 sm:space-y-3.5">
            <div class="flex items-center gap-1.5 pb-1.5 border-b border-slate-100">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-photo-film text-pink-600"></i> Media: Foto &amp; Video Properti
                </h2>
                <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                    <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Media">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                        Atur foto, pilih cover utama, dan video walkthrough.
                        <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                    </div>
                </div>
            </div>

            <!-- 1. BAGIAN FOTO -->
            <div class="space-y-2.5">
                <div class="flex items-center gap-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800">
                        Foto Properti Saat Ini &amp; Baru
                    </label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Foto">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-60 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Pilih foto mana yang menjadi Cover utama. Gunakan tombol panah untuk menggeser urutan foto.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>

                <!-- Foto yang Sudah Ada di Database (Dapat diatur urutannya, dijadikan cover, atau dihapus) -->
                <template x-if="existingImages.length > 0">
                    <div class="space-y-2 pb-2.5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-700">
                                Foto Tersimpan Saat Ini (<span x-text="existingImages.length"></span> foto):
                            </p>
                        </div>

                        <!-- Hidden Inputs to preserve existing images and their exact order -->
                        <template x-for="(img, idx) in existingImages" :key="'exist-input-'+idx">
                            <input type="hidden" name="keep_images[]" :value="img">
                        </template>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(img, idx) in existingImages" :key="'exist-'+idx">
                                <div :class="idx === 0 ? 'ring-2 ring-pink-600 border-pink-600 shadow-md' : 'border-slate-200 shadow-2xs'"
                                     class="group relative rounded-xl overflow-hidden border bg-white flex flex-col justify-between transition">
                                    
                                    <!-- Thumbnail -->
                                    <div class="aspect-[16/10] w-full overflow-hidden bg-slate-900 relative">
                                        <img :src="img.startsWith('http') ? img : '/storage/' + img" class="w-full h-full object-cover">

                                        <!-- Badge Cover Utama -->
                                        <template x-if="idx === 0">
                                            <span class="absolute top-1.5 left-1.5 z-10 bg-pink-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow flex items-center gap-1">
                                                <i class="fa-solid fa-star text-[8px]"></i> COVER UTAMA
                                            </span>
                                        </template>

                                        <!-- Tombol Hapus Pojok Kanan Atas -->
                                        <button type="button"
                                                @click="removeExisting(idx)"
                                                title="Hapus Foto Ini"
                                                class="absolute top-1.5 right-1.5 z-10 w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    <!-- Bottom Control Bar -->
                                    <div class="p-1.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-1">
                                        <!-- Tombol Geser Kiri -->
                                        <button type="button"
                                                @click="moveExisting(idx, -1)"
                                                :disabled="idx === 0"
                                                :class="idx === 0 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50 cursor-pointer'"
                                                title="Geser ke Kiri"
                                                class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-xs transition">
                                            <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                        </button>

                                        <!-- Tombol Jadikan Cover -->
                                        <template x-if="idx !== 0">
                                            <button type="button"
                                                    @click="setCoverExisting(idx)"
                                                    title="Jadikan Foto Ini Sebagai Cover Utama"
                                                    class="flex-1 py-0.5 px-1 rounded-lg bg-pink-50 hover:bg-pink-600 hover:text-white text-pink-700 font-extrabold text-[10px] border border-pink-200 transition text-center cursor-pointer">
                                                Jadikan Cover
                                            </button>
                                        </template>
                                        <template x-if="idx === 0">
                                            <span class="flex-1 text-center font-black text-[10px] text-pink-600">
                                                Foto Utama
                                            </span>
                                        </template>

                                        <!-- Tombol Geser Kanan -->
                                        <button type="button"
                                                @click="moveExisting(idx, 1)"
                                                :disabled="idx === existingImages.length - 1"
                                                :class="idx === existingImages.length - 1 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50 cursor-pointer'"
                                                title="Geser ke Kanan"
                                                class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-xs transition">
                                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Dropzone Upload File Baru -->
                <div class="relative border-2 border-dashed border-pink-200 hover:border-pink-400 bg-pink-50/20 hover:bg-pink-50/40 rounded-2xl p-4 sm:p-5 text-center transition-colors">
                    <input type="file"
                           id="image_files_input"
                           name="image_files[]"
                           multiple
                           accept="image/jpeg,image/png,image/webp,image/jpg"
                           @change="handleFileSelect($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                    <div class="flex flex-col items-center pointer-events-none space-y-1">
                        <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-lg shadow-xs">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-black text-slate-800">
                            + Tambah Foto Baru dari Komputer
                        </p>
                        <p class="text-xs text-slate-400">
                            Pilih beberapa file foto sekaligus (JPG, PNG, WEBP maks 10MB per foto)
                        </p>
                    </div>
                </div>

                <!-- Live Previews Foto Baru -->
                <template x-if="newImagePreviews.length > 0">
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-700">
                                Foto Baru yang akan Diunggah (<span x-text="newImagePreviews.length"></span> foto):
                            </p>
                            <button type="button"
                                    @click="clearFiles()"
                                    class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-trash-can text-xs"></i> Reset Foto Baru
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(img, idx) in newImagePreviews" :key="'new-'+idx">
                                <div class="group relative rounded-xl overflow-hidden border border-slate-200 shadow-2xs bg-white flex flex-col justify-between transition">
                                    <div class="aspect-[16/10] w-full overflow-hidden bg-slate-900 relative">
                                        <img :src="img.url" class="absolute inset-0 w-full h-full object-cover blur-md opacity-40 scale-110">
                                        <img :src="img.url" class="relative z-1 w-full h-full object-cover">

                                        <!-- Tombol Hapus -->
                                        <button type="button"
                                                @click="removePreview(idx)"
                                                title="Batal Unggah Foto Ini"
                                                class="absolute top-1.5 right-1.5 z-10 w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    <!-- Bottom Control Bar -->
                                    <div class="p-1.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-1">
                                        <button type="button"
                                                @click="movePreview(idx, -1)"
                                                :disabled="idx === 0"
                                                :class="idx === 0 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50 cursor-pointer'"
                                                title="Geser ke Kiri"
                                                class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-xs transition">
                                            <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                        </button>

                                        <button type="button"
                                                @click="setCover(idx)"
                                                title="Pindahkan Foto Baru Ini ke Depan"
                                                class="flex-1 py-0.5 px-1 rounded-lg bg-pink-50 hover:bg-pink-600 hover:text-white text-pink-700 font-extrabold text-[10px] border border-pink-200 transition text-center cursor-pointer">
                                            Paling Depan
                                        </button>

                                        <button type="button"
                                                @click="movePreview(idx, 1)"
                                                :disabled="idx === newImagePreviews.length - 1"
                                                :class="idx === newImagePreviews.length - 1 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50 cursor-pointer'"
                                                title="Geser ke Kanan"
                                                class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-xs transition">
                                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Info & Preview Foto Default jika Belum Ada Foto yang Diunggah atau Disimpan -->
                <template x-if="existingImages.length === 0 && newImagePreviews.length === 0">
                    <div class="mt-2.5 p-3 rounded-2xl bg-slate-50/80 border border-slate-200/90 flex items-center gap-3">
                        <div class="w-16 h-12 rounded-xl overflow-hidden border border-slate-200 shrink-0 bg-slate-100 shadow-2xs">
                            <img :src="getDefaultPhoto()" class="w-full h-full object-cover" alt="Foto Default">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-pink-100 text-pink-700 uppercase tracking-wider">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Foto Default Sesuai Tipe
                                </span>
                                <span class="text-xs font-bold text-slate-800 capitalize" x-text="'Kategori: ' + propertyType"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                Semua foto dikosongkan. Jika disimpan tanpa foto baru, sistem otomatis menyematkan foto default di samping sesuai kategori <strong class="text-slate-700 capitalize" x-text="propertyType"></strong>.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Input Textarea untuk URL Foto Eksternal (Opsional) -->
                <div class="pt-1">
                    <label class="block text-xs font-bold text-slate-600 mb-0.5">
                        Atau Tambahkan URL Foto Eksternal (Opsional, 1 URL per baris)
                    </label>
                    <textarea name="images_text"
                              rows="2"
                              placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..."
                              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-pink-500 focus:outline-none">{{ old('images_text') }}</textarea>
                </div>
            </div>

            <!-- 2. BAGIAN VIDEO TOUR -->
            <div class="pt-2.5 border-t border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-video text-pink-600"></i> Video <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                    </label>
                    <span class="text-xs bg-slate-50 text-pink-700 px-2 py-0.5 rounded font-extrabold border border-pink-200">
                        MP4, MOV / YouTube / TikTok
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Upload File Video Baru (Maks. 50 MB)</label>
                        <input type="file"
                               name="video_file"
                               accept="video/mp4,video/quicktime,video/webm"
                               class="w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-2.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 cursor-pointer border border-slate-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Link YouTube / TikTok / Reels</label>
                        <input type="url"
                               name="video_url"
                               placeholder="https://www.youtube.com/watch?v=... atau https://vt.tiktok.com/..."
                               value="{{ old('video_url', $property->video_url) }}"
                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 9. DATA PEMILIK & CATATAN INTERNAL -->
        <!-- ======================================================== -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-address-card text-pink-600"></i> Data Pemilik
                </h2>
                <span class="text-xs text-pink-600 font-bold bg-pink-50 px-2.5 py-0.5 rounded-lg border border-pink-200 flex items-center gap-1">
                    <i class="fa-solid fa-shield-halved text-xs"></i> Rahasia (Hanya Agen &amp; Admin)
                </span>
            </div>

            <!-- Tipe Pemilik (Vendor vs Agent) -->
            <div class="space-y-3">
                <div class="max-w-xs">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Pemilik</label>
                    <div class="relative">
                        <select name="owner_type"
                                x-model="ownerType"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none transition appearance-none pr-8">
                            <option value="vendor">Vendor</option>
                            <option value="agent">Agent</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-xs text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5" x-text="ownerType === 'agent' ? 'Nama Pemilik/Agen' : 'Nama Pemilik'"></label>
                        <input type="text"
                               name="owner_name"
                               :placeholder="ownerType === 'agent' ? 'Masukkan Nama (Opsional)' : 'Cth: Bpk. Hendra'"
                               value="{{ old('owner_name', $property->owner_name) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">No. WhatsApp</label>
                        <input type="text"
                               name="owner_phone"
                               placeholder="Cth: 081234567 (Opsional)"
                               value="{{ old('owner_phone', $property->owner_phone) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                    </div>

                    <div x-show="ownerType === 'agent'" x-cloak>
                        <label class="block text-xs font-bold text-slate-700 mb-0.5">Kantor</label>
                        <input type="text"
                               name="owner_office"
                               placeholder="Cth: Xavier Marks, Era Galaxy, dll."
                               value="{{ old('owner_office', $property->owner_office) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none transition">
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <label class="block text-xs font-bold text-slate-700">Catatan Internal Listing (Private)</label>
                    <div class="relative inline-flex items-center" x-data="{ tip: false }" @mouseenter="tip = true" @mouseleave="tip = false">
                        <button type="button" @click="tip = !tip" class="w-5 h-5 rounded-full text-pink-500 hover:text-pink-600 hover:bg-pink-50 flex items-center justify-center transition cursor-pointer" aria-label="Info Catatan Internal">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </button>
                        <div x-show="tip" x-cloak @click.outside="tip = false" class="absolute left-0 sm:left-1/2 sm:-translate-x-1/2 bottom-full mb-2 w-64 p-2.5 rounded-xl bg-slate-900 text-white text-[11px] leading-relaxed shadow-xl z-30 pointer-events-none">
                            Catatan ini bersifat internal dan tidak akan pernah ditampilkan di website publik.
                            <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                            <div class="sm:hidden absolute left-2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-slate-900"></div>
                        </div>
                    </div>
                </div>
                <textarea name="internal_notes"
                          rows="3"
                          placeholder="Tuliskan catatan rahasia listing di sini (misal: kunci ada di satpam / titip tetangga, nego tipis, jadwal survei sore hari, komisi disepakati 2.5%, dll)..."
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none transition">{{ old('internal_notes', $property->internal_notes) }}</textarea>
            </div>

            <!-- Status Publikasi & Badges -->
            <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $property->is_featured) ? 'checked' : '' }} class="rounded text-pink-600 focus:ring-pink-500">
                        <span>Pilihan Unggulan</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-pink-700 bg-pink-50 px-2.5 py-1 rounded-lg border border-pink-200">
                        <input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent', $property->is_urgent) ? 'checked' : '' }} class="rounded text-pink-600 focus:ring-pink-500">
                        <span><i class="fa-solid fa-fire mr-1 text-pink-600"></i> Butuh Laku Cepat (BU)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                        <input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $property->is_verified) ? 'checked' : '' }} class="rounded text-pink-600 focus:ring-pink-500">
                        <span>Verified Listing</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Status:</span>
                    <select name="status" required class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-xs font-bold text-pink-700 focus:ring-2 focus:ring-pink-500">
                        <option value="active" {{ old('status', $property->status) === 'active' ? 'selected' : '' }}>Active (Tayang)</option>
                        <option value="sold" {{ old('status', $property->status) === 'sold' ? 'selected' : '' }}>Sold (Terjual)</option>
                        <option value="rented" {{ old('status', $property->status) === 'rented' ? 'selected' : '' }}>Rented (Tersewa)</option>
                        <option value="pending" {{ old('status', $property->status) === 'pending' ? 'selected' : '' }}>Pending (Draf)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.properties') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <a href="{{ route('properties.show', $property->slug) }}" target="_blank" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-bold hover:bg-slate-50 transition">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Lihat Tayang
                </a>
            </div>

            <button type="submit"
                    :disabled="isSubmitting"
                    :class="isSubmitting ? 'opacity-70 cursor-not-allowed pointer-events-none' : ''"
                    class="w-full sm:w-auto px-8 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-black shadow-md shadow-pink-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-spinner fa-spin text-sm" x-show="isSubmitting" x-cloak></i>
                <i class="fa-solid fa-floppy-disk text-sm" x-show="!isSubmitting"></i>
                <span x-text="isSubmitting ? 'Sedang Menyimpan Perubahan...' : 'Simpan Perubahan Listing'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
