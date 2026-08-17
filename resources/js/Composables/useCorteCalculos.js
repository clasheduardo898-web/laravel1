const LIBRAS_POR_KG = 2.2046;
const MM_POR_PULGADA = 25.4;
export const MAX_ROLLOS_POR_CORTE = 30;

export function anchoEnMm(numCorte, anchoValor) {
    const valor = parseFloat(anchoValor) || 0;
    return (numCorte.unidad_ancho ?? 'mm') === 'pulgada' ? valor * MM_POR_PULGADA : valor;
}

export function totalAnchoNumCorte(numCorte) {
    return numCorte.rollos.reduce((acc, r) => acc + (parseFloat(r.ancho) || 0), 0);
}

export function coreParteLb(numCorte, anchoIndividual) {
    const totalAncho = totalAnchoNumCorte(numCorte);
    const core = parseFloat(numCorte.core_lb) || 0;
    if (totalAncho <= 0) return 0;
    return (core / totalAncho) * (parseFloat(anchoIndividual) || 0);
}

export function pesoNetoLb(numCorte, pesoBruto, anchoIndividual) {
    return Math.max(0, (parseFloat(pesoBruto) || 0) - coreParteLb(numCorte, anchoIndividual));
}

export function pesoKg(pesoNeto) {
    return pesoNeto ? Math.round((pesoNeto / LIBRAS_POR_KG) * 1000) / 1000 : 0;
}

export function totalAncho(numerosCorte) {
    return numerosCorte.reduce(
        (total, nc) => total + nc.rollos.reduce((sub, r) => sub + anchoEnMm(nc, r.ancho), 0),
        0
    );
}

export function totalPesoNetoKg(numerosCorte) {
    let total = 0;
    for (const nc of numerosCorte) {
        for (const r of nc.rollos) {
            total += pesoKg(pesoNetoLb(nc, r.peso_lb, r.ancho));
        }
    }
    return Math.round(total * 1000) / 1000;
}

export function nuevoNumeroCorte(indiceActual) {
    return {
        numero: String(indiceActual + 1),
        core_lb: '',
        unidad_ancho: 'mm',
        rollos: [{ ancho: '', peso_lb: '' }],
    };
}