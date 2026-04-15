import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

export default function SiMakan() {
    return (
        <div className="p-8 bg-gray-50 min-h-screen">
            <h1 className="text-3xl font-bold mb-8">Menu Kantin SiMakan</h1>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* Ini adalah Card yang kamu pindahkan dari Figma */}
                <Card>
                    <CardHeader>
                        <CardTitle>Nasi Goreng Spesial</CardTitle>
                        <CardDescription>Rp 15.000</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p className="mb-4 text-sm text-gray-600">Nasi goreng dengan topping telur dan bakso.</p>
                        <Button className="w-full">Pesan Sekarang</Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}