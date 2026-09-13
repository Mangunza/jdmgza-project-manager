<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Catálogo inicial de serviços da empresa.
     *
     * O seeder é idempotente:
     * executar novamente atualiza os serviços existentes
     * em vez de criar duplicados.
     */
    public function run(): void
    {
        $services = [
            // Análise e Planejamento
            [
                'name' => 'Análise de Requisitos',
                'description' => 'Análise das necessidades, objetivos e requisitos do projeto.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Levantamento de Requisitos',
                'description' => 'Levantamento e documentação dos requisitos funcionais e não funcionais.',
                'default_cost' => 40000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Consultoria em Software',
                'description' => 'Consultoria técnica para definição de soluções e estratégias de software.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Arquitetura de Software',
                'description' => 'Definição da arquitetura, componentes, integrações e tecnologias do sistema.',
                'default_cost' => 100000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Planejamento de Projeto',
                'description' => 'Planejamento técnico e funcional do projeto de software.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Documentação Técnica',
                'description' => 'Criação e organização da documentação técnica do sistema.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],

            // UI/UX e Design
            [
                'name' => 'UX/UI Design',
                'description' => 'Planejamento da experiência e interface do utilizador.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Wireframe',
                'description' => 'Criação de wireframes para estruturar as interfaces do sistema.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Protótipo Interativo',
                'description' => 'Criação de protótipo navegável para validação da solução.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Design de Interface Web',
                'description' => 'Design de interfaces para aplicações e plataformas web.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Design de Interface Mobile',
                'description' => 'Design de interfaces para aplicações móveis.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Design System',
                'description' => 'Criação de sistema de componentes e padrões visuais reutilizáveis.',
                'default_cost' => 80000.00,
                'is_active' => true,
            ],

            // Desenvolvimento Web
            [
                'name' => 'Website Institucional',
                'description' => 'Desenvolvimento de website institucional para empresas e organizações.',
                'default_cost' => 150000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Landing Page',
                'description' => 'Desenvolvimento de página de conversão ou apresentação de produto.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Aplicação Web',
                'description' => 'Desenvolvimento de aplicação web personalizada.',
                'default_cost' => 250000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sistema Web Personalizado',
                'description' => 'Desenvolvimento de sistema web personalizado de acordo com os requisitos do cliente.',
                'default_cost' => 350000.00,
                'is_active' => true,
            ],
            [
                'name' => 'E-commerce',
                'description' => 'Desenvolvimento de plataforma de comércio eletrónico.',
                'default_cost' => 300000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Portal Web',
                'description' => 'Desenvolvimento de portal web com múltiplas áreas e funcionalidades.',
                'default_cost' => 350000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Dashboard Web',
                'description' => 'Desenvolvimento de dashboards para visualização e análise de dados.',
                'default_cost' => 180000.00,
                'is_active' => true,
            ],

            // Desenvolvimento Mobile
            [
                'name' => 'Aplicação Android',
                'description' => 'Desenvolvimento de aplicação móvel para Android.',
                'default_cost' => 250000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Aplicação iOS',
                'description' => 'Desenvolvimento de aplicação móvel para iOS.',
                'default_cost' => 250000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Aplicação Mobile Multiplataforma',
                'description' => 'Desenvolvimento de aplicação mobile para múltiplas plataformas.',
                'default_cost' => 300000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Publicação na Google Play',
                'description' => 'Preparação e publicação da aplicação na Google Play.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Publicação na App Store',
                'description' => 'Preparação e publicação da aplicação na App Store.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],

            // Backend e APIs
            [
                'name' => 'Desenvolvimento de API REST',
                'description' => 'Desenvolvimento de API REST para integração entre aplicações e serviços.',
                'default_cost' => 150000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Integração com API Externa',
                'description' => 'Integração do sistema com APIs e serviços externos.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sistema de Autenticação',
                'description' => 'Implementação de autenticação e gestão de sessões de utilizadores.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sistema de Autorização',
                'description' => 'Implementação de roles, permissões e controlo de acesso.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Desenvolvimento de Módulo Backend',
                'description' => 'Desenvolvimento de módulos e funcionalidades específicas no backend.',
                'default_cost' => 150000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Modelagem de Banco de Dados',
                'description' => 'Modelagem estrutural e relacional da base de dados.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Integração com Banco de Dados',
                'description' => 'Configuração e integração da aplicação com banco de dados.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],

            // Cloud, DevOps e Deploy
            [
                'name' => 'Configuração de Servidor',
                'description' => 'Configuração e preparação do ambiente de servidor.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Deploy de Aplicação',
                'description' => 'Publicação da aplicação em ambiente de produção.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Configuração de Domínio',
                'description' => 'Configuração de domínio e respetivos apontamentos DNS.',
                'default_cost' => 20000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Configuração de SSL',
                'description' => 'Configuração de certificado SSL e HTTPS.',
                'default_cost' => 20000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Configuração de CI/CD',
                'description' => 'Configuração de pipelines de integração e entrega contínua.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Configuração de Backup',
                'description' => 'Configuração de políticas e rotinas de backup.',
                'default_cost' => 40000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Monitoramento de Aplicação',
                'description' => 'Configuração de monitoramento e observabilidade da aplicação.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],

            // Qualidade
            [
                'name' => 'Testes Funcionais',
                'description' => 'Execução e validação das funcionalidades do sistema.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Testes de API',
                'description' => 'Validação dos endpoints e contratos da API.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Testes de Integração',
                'description' => 'Validação da integração entre módulos e serviços.',
                'default_cost' => 60000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Testes de Performance',
                'description' => 'Avaliação do desempenho e comportamento da aplicação sob carga.',
                'default_cost' => 80000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Correção de Bugs',
                'description' => 'Identificação e correção de erros encontrados na aplicação.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Auditoria Técnica',
                'description' => 'Avaliação técnica da arquitetura, código e infraestrutura.',
                'default_cost' => 100000.00,
                'is_active' => true,
            ],

            // Manutenção e Suporte
            [
                'name' => 'Manutenção de Website',
                'description' => 'Manutenção corretiva e evolutiva de websites.',
                'default_cost' => 50000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Manutenção de Aplicação Web',
                'description' => 'Manutenção corretiva e evolutiva de aplicações web.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Manutenção de Aplicação Mobile',
                'description' => 'Manutenção corretiva e evolutiva de aplicações mobile.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Suporte Técnico',
                'description' => 'Suporte técnico para utilização e resolução de problemas.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Atualização de Dependências',
                'description' => 'Atualização e manutenção das dependências do projeto.',
                'default_cost' => 40000.00,
                'is_active' => true,
            ],

            // Segurança
            [
                'name' => 'Auditoria de Segurança',
                'description' => 'Avaliação da segurança da aplicação e da infraestrutura.',
                'default_cost' => 120000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Hardening de Servidor',
                'description' => 'Aplicação de medidas de reforço da segurança do servidor.',
                'default_cost' => 80000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Implementação de HTTPS',
                'description' => 'Configuração e implementação de comunicação HTTPS.',
                'default_cost' => 30000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Análise de Vulnerabilidades',
                'description' => 'Identificação e análise de potenciais vulnerabilidades de segurança.',
                'default_cost' => 100000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Implementação de Controle de Acesso',
                'description' => 'Implementação de mecanismos de controlo de acesso e permissões.',
                'default_cost' => 75000.00,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
